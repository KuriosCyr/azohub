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

            $record->prestataire->creditWallet((float) $record->amount);

            $record->update([
                'status' => 'rejected',
                'admin_note' => $reason,
                'processed_by' => $adminId,
                'processed_at' => now(),
            ]);
        });

        $this->refresh();
    }
}
