<?php

namespace App\Livewire;

use App\Models\Proposal;
use App\Models\ServiceRequest;
use App\Notifications\NewProposalReceived;
use App\Notifications\ProposalRejected;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ServiceRequestShow extends Component
{
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

        $proposal = $this->serviceRequest->proposals()
            ->where('id', $proposalId)
            ->where('status', 'pending')
            ->firstOrFail();

        $proposal->update(['status' => 'rejected']);
        $proposal->prestataire->notify(new ProposalRejected($proposal));

        $this->loadRequest();

        session()->flash('success', 'Proposition refusée.');
    }

    public function render()
    {
        return view('livewire.service-request-show')
            ->layout('components.layouts.app');
    }
}
