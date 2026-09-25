<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PrestataireWallet extends Component
{
    use WithPagination;

    public const MIN_WITHDRAWAL = 2000;

    public bool $showRequestForm = false;
    public $amount = '';
    public string $paymentMethod = 'mtn_momo';
    public string $phoneNumber = '';

    public function mount()
    {
        $this->phoneNumber = Auth::user()->phone ?? '';
    }

    public function toggleRequestForm()
    {
        $this->showRequestForm = !$this->showRequestForm;
    }

    public function requestWithdrawal()
    {
        if (!Auth::user()->identity_verified) {
            $this->addError('amount', 'Votre identité doit être vérifiée avant de pouvoir retirer des fonds.');
            return;
        }

        $balance = (float) Auth::user()->wallet_balance;

        $this->validate([
            'amount' => 'required|numeric|min:' . self::MIN_WITHDRAWAL . '|max:' . max($balance, self::MIN_WITHDRAWAL),
            'paymentMethod' => 'required|in:mtn_momo,moov_money,celtiis_cash',
            // BUG021 : depuis la migration du plan de numérotation béninois (2023), tout numéro
            // mobile fait 10 chiffres et commence par 01 — un ancien numéro à 8 chiffres saisi
            // sans ce préfixe est accepté ici mais rejeté par l'opérateur Mobile Money au moment
            // du virement réel, sans qu'on en soit informé avant que l'admin tente le paiement.
            'phoneNumber' => ['required', 'string', function ($attribute, $value, $fail) {
                $digits = preg_replace('/[^0-9]/', '', $value);
                if (str_starts_with($digits, '229') && strlen($digits) > 10) {
                    $digits = substr($digits, 3);
                }
                if (!preg_match('/^01\d{8}$/', $digits)) {
                    $fail('Le numéro doit être au format béninois à 10 chiffres commençant par 01 (ex : 01 23 45 67 89).');
                }
            }],
        ], [
            'amount.required' => 'Veuillez indiquer un montant.',
            'amount.min' => 'Le montant minimum de retrait est de ' . number_format(self::MIN_WITHDRAWAL, 0, ',', ' ') . ' FCFA.',
            'amount.max' => 'Le montant demandé dépasse votre solde disponible.',
            'phoneNumber.required' => 'Veuillez indiquer un numéro de réception.',
        ]);

        $amount = (float) $this->amount;

        $withdrawal = DB::transaction(function () use ($amount) {
            $user = User::where('id', Auth::id())->lockForUpdate()->first();

            if (!$user->debitWallet($amount)) {
                return null;
            }

            return WithdrawalRequest::create([
                'prestataire_id' => $user->id,
                'amount' => $amount,
                'payment_method' => $this->paymentMethod,
                'phone_number' => $this->phoneNumber,
                'status' => 'pending',
            ]);
        });

        if (!$withdrawal) {
            $this->addError('amount', 'Solde insuffisant.');
            return;
        }

        $this->reset(['amount', 'showRequestForm']);
        session()->flash('success', 'Votre demande de retrait a été envoyée. Elle sera traitée sous peu.');
    }

    public function render()
    {
        $user = Auth::user();

        $withdrawals = $user->withdrawalRequests()->latest()->paginate(10, ['*'], 'withdrawals');

        $earnings = Order::where('prestataire_id', $user->id)
            ->where('payment_status', 'released')
            ->with(['service', 'serviceRequest.category', 'customOffer'])
            ->latest('validated_at')
            ->paginate(10, ['*'], 'earnings');

        return view('livewire.prestataire-wallet', [
            'withdrawals' => $withdrawals,
            'earnings' => $earnings,
        ])->layout('components.layouts.app');
    }
}
