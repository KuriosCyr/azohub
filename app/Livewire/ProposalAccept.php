<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Proposal;
use App\Models\ServiceRequest;
use App\Notifications\ProposalRejected;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProposalAccept extends Component
{
    public ServiceRequest $serviceRequest;
    public Proposal $proposal;
    public string $paymentMethod = 'mtn_momo';

    public function mount(ServiceRequest $serviceRequest, Proposal $proposal)
    {
        abort_unless($serviceRequest->client_id === Auth::id(), 403);
        abort_unless($proposal->service_request_id === $serviceRequest->id, 404);
        abort_unless($proposal->status === 'pending', 403, 'Cette proposition ne peut plus être acceptée.');
        abort_unless($serviceRequest->status === 'open', 403, 'Cette demande n\'est plus ouverte.');

        $this->serviceRequest = $serviceRequest->load('category');
        $this->proposal = $proposal->load('prestataire');
    }

    public function confirm(PaymentService $payments)
    {
        $this->validate([
            'paymentMethod' => 'required|in:mtn_momo,moov_money,celtiis_cash,card',
        ]);

        $amount = (float) $this->proposal->proposed_price;
        $commission = round($amount * 0.10, 2);

        $previouslyPendingIds = $this->serviceRequest->proposals()
            ->where('id', '!=', $this->proposal->id)
            ->where('status', 'pending')
            ->pluck('id');

        $order = DB::transaction(function () use ($amount, $commission) {
            $order = Order::create([
                'client_id' => Auth::id(),
                'prestataire_id' => $this->proposal->user_id,
                'service_request_id' => $this->serviceRequest->id,
                'proposal_id' => $this->proposal->id,
                'requirements' => $this->serviceRequest->description,
                'amount' => $amount,
                'commission' => $commission,
                'prestataire_amount' => $amount - $commission,
                'delivery_time' => $this->proposal->delivery_time,
                'status' => 'pending_payment',
                'payment_status' => 'pending',
            ]);

            $this->proposal->accept();

            return $order;
        });

        foreach (Proposal::whereIn('id', $previouslyPendingIds)->with('prestataire')->get() as $rejected) {
            $rejected->prestataire->notify(new ProposalRejected($rejected));
        }

        try {
            $url = $payments->initiateForOrder($order, $this->paymentMethod);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('orders.show', $order)
                ->with('error', 'La commande a été créée mais le paiement n\'a pas pu être initié. Réessayez depuis la page de la commande.');
        }

        return redirect()->away($url);
    }

    public function render()
    {
        return view('livewire.proposal-accept')
            ->layout('components.layouts.app');
    }
}
