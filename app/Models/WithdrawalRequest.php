<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'prestataire_id',
        'amount',
        'payment_method',
        'phone_number',
        'status',
        'admin_note',
        'processed_by',
        'processed_at',
        'fedapay_payout_id',
        'fedapay_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function prestataire()
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Le solde a déjà été débité à la création de la demande (voir PrestataireWallet::requestWithdrawal).
    // Verrouillé + gardé par le statut pour éviter qu'un double clic admin (ou deux
    // admins sur la même demande) ne traite deux fois la même demande.
    public function markAsPaid(int $adminId): void
    {
        DB::transaction(function () use ($adminId) {
            $record = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$record || $record->status !== 'pending') {
                return;
            }

            $record->update([
                'status' => 'paid',
                'processed_by' => $adminId,
                'processed_at' => now(),
            ]);
        });

        $this->refresh();
    }

    // Recrédite le prestataire puisque le montant avait été débité à la demande.
    // Même garde que markAsPaid() : sans elle, un double clic recréditerait deux fois.
    public function reject(int $adminId, string $reason): void
    {
        DB::transaction(function () use ($adminId, $reason) {
            $record = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$record || $record->status !== 'pending') {
                return;
            }

            $record->prestataire->creditWallet(
                (float) $record->amount,
                'Retrait rejeté : ' . $reason,
                $record
            );

            $record->update([
                'status' => 'rejected',
                'admin_note' => $reason,
                'processed_by' => $adminId,
                'processed_at' => now(),
            ]);
        });

        $this->refresh();
    }

    // Un virement FedaPay vient d'être déclenché (PaymentService::initiatePayout) : marque
    // seulement qu'un essai est EN COURS (fedapay_payout_id), sans toucher au statut métier
    // (reste 'pending') tant que la confirmation (webhook) n'est pas arrivée — voir
    // canRetryFedapayPayout(), qui empêche un second essai tant que celui-ci n'a pas échoué.
    public function markFedapayPayoutSent(string $payoutId): void
    {
        $this->update([
            'fedapay_payout_id' => $payoutId,
            'fedapay_status' => 'pending',
        ]);
    }

    public function canRetryFedapayPayout(): bool
    {
        return $this->status === 'pending' && $this->fedapay_payout_id === null;
    }

    // Confirmation FedaPay (webhook) que le virement a bien été envoyé : même effet que
    // markAsPaid(), mais processed_by reste null (déclenché par le système, pas un admin).
    // Verrouillée comme markAsPaid()/reject() : un événement livré deux fois par FedaPay ne
    // doit pas repasser deux fois par cette méthode.
    public function confirmFedapayPayout(): void
    {
        DB::transaction(function () {
            $record = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$record || $record->status !== 'pending') {
                return;
            }

            $record->update([
                'status' => 'paid',
                'fedapay_status' => 'sent',
                'processed_at' => now(),
            ]);
        });

        $this->refresh();
    }

    // Échec confirmé par FedaPay (webhook) : recrédite le portefeuille (comme reject()) mais
    // laisse la demande 'pending' — ni payée ni rejetée, l'admin peut réessayer via l'API ou
    // traiter manuellement, sans avoir à en recréer une.
    public function failFedapayPayout(string $reason): void
    {
        DB::transaction(function () use ($reason) {
            $record = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$record || $record->status !== 'pending' || $record->fedapay_status !== 'pending') {
                return;
            }

            $record->prestataire->creditWallet(
                (float) $record->amount,
                'Virement FedaPay échoué : ' . $reason,
                $record
            );

            $record->update([
                'fedapay_status' => 'failed',
                'fedapay_payout_id' => null,
                'admin_note' => trim(($record->admin_note ?? '') . "\nÉchec FedaPay : {$reason}"),
            ]);
        });

        $this->refresh();
    }
}
