<?php

namespace App\Livewire;

use App\Livewire\Concerns\RateLimitsActions;
use App\Models\Order;
use App\Models\Proposal;
use App\Models\ServiceRequest;
use App\Notifications\NewProposalReceived;
use App\Notifications\ProposalRejected;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ServiceRequestShow extends Component
{
    use RateLimitsActions;

    public ServiceRequest $serviceRequest;

    public string $message = '';
    public $proposedPrice = '';
    public $deliveryTime = '';

    protected $rules = [
        'message' => 'required|string|min:10|max:1500',
        'proposedPrice' => 'required|numeric|min:100|max:100000000',
        'deliveryTime' => 'required|integer|min:1|max:365',
    ];

    protected $messages = [
        'message.required' => 'Veuillez présenter votre proposition.',
        'message.min' => 'Votre message doit faire au moins 10 caractères.',
        'proposedPrice.required' => 'Veuillez indiquer votre prix.',
        'deliveryTime.required' => 'Veuillez indiquer votre délai de livraison.',
    ];

    public function mount(ServiceRequest $serviceRequest)
    {
        $this->serviceRequest = $serviceRequest;
        $this->loadRequest();
    }

    protected function loadRequest()
    {
        $this->serviceRequest = $this->serviceRequest->fresh([
            'client', 'category', 'proposals.prestataire',
        ]);
    }

    public function getMyProposalProperty()
    {
        if (!Auth::check() || !Auth::user()->isPrestataire()) {
            return null;
        }

        return $this->serviceRequest->proposals->firstWhere('user_id', Auth::id());
    }

    public function getIsOwnerProperty(): bool
    {
        return Auth::check() && $this->serviceRequest->client_id === Auth::id();
    }

    public function submitProposal()
    {
        abort_unless(Auth::check() && Auth::user()->isPrestataire(), 403);
        abort_if($this->serviceRequest->status !== 'open', 403, 'Cette demande n\'est plus ouverte aux propositions.');
        abort_if($this->myProposal, 403, 'Vous avez déjà soumis une proposition pour cette demande.');

        if ($this->tooManyActions('submit-proposal', maxAttempts: 10, decayMinutes: 60, field: 'message')) {
            return;
        }

        $validated = $this->validate();

        try {
            $proposal = Proposal::create([
                'service_request_id' => $this->serviceRequest->id,
                'user_id' => Auth::id(),
                'message' => $validated['message'],
                'proposed_price' => $validated['proposedPrice'],
                'delivery_time' => $validated['deliveryTime'],
                'status' => 'pending',
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Double clic : la proposition existe déjà (index unique), on ne la duplique pas.
            $this->loadRequest();
            return;
        }

        $this->serviceRequest->increment('proposals_count');
        $this->serviceRequest->client->notify(new NewProposalReceived($proposal));

        $this->reset(['message', 'proposedPrice', 'deliveryTime']);
        $this->loadRequest();

        session()->flash('success', 'Votre proposition a été envoyée avec succès !');
    }

    public function rejectProposal($proposalId)
    {
        abort_unless($this->isOwner, 403);

        // Verrouillée comme ProposalAccept::confirm() (audit externe — 7e audit) : sans verrou,
        // un refus et une acceptation concurrents (même client, deux onglets, au même instant)
        // pouvaient se croiser — la vérification "paiement en cours" ci-dessous passant à false
        // juste avant que confirm() ne crée la commande.
        $result = DB::transaction(function () use ($proposalId) {
            $proposal = $this->serviceRequest->proposals()
                ->where('id', $proposalId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$proposal) {
                return 'not_found';
            }

            // ProposalAccept::confirm() crée la commande SANS jamais changer proposal.status
            // (reste 'pending' tant que le paiement n'est pas confirmé) — un paiement peut donc
            // déjà être en cours pour cette proposition au moment du rejet. Si le client paie
            // ensuite, la commande s'active quand même (Payment::finalizeNegotiatedOrder()
            // n'accepte que les propositions encore 'pending'), mais la demande reste 'open' à
            // tort : les autres prestataires continuent d'y avoir accès, et le prestataire
            // réellement payé n'a pas la proposition 'accepted' qui le protégerait (audit
            // externe — 6e audit). Refuser le rejet dans ce cas plutôt que de laisser cet état
            // incohérent se produire.
            if (Order::where('proposal_id', $proposal->id)->where('status', 'pending_payment')->exists()) {
                return 'payment_in_progress';
            }

            $proposal->update(['status' => 'rejected']);

            return $proposal;
        });

        if ($result === 'not_found') {
            abort(404);
        }

        if ($result === 'payment_in_progress') {
            // Précise maintenant comment débloquer la situation (audit externe — 7e audit) : une
            // tentative de paiement abandonnée (jamais finalisée) bloquait le rejet jusqu'à son
            // expiration automatique (24h, cf. ExpireStalePendingPayments), sans que le client
            // sache qu'il pouvait l'annuler lui-même pour débloquer immédiatement.
            session()->flash('error', 'Un paiement est en cours pour cette proposition : elle ne peut plus être refusée pour le moment. Si cette tentative de paiement est abandonnée, vous pouvez l\'annuler depuis la page de la commande pour débloquer la situation.');

            return;
        }

        $result->prestataire->notify(new ProposalRejected($result));

        $this->loadRequest();

        session()->flash('success', 'Proposition refusée.');
    }

    public function render()
    {
        return view('livewire.service-request-show')
            ->layout('components.layouts.app');
    }
}
