<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    public function markAsPaid(int $adminId): void
    {
        $this->update([
            'status' => 'paid',
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);
    }

    // Recrédite le prestataire puisque le montant avait été débité à la demande.
    public function reject(int $adminId, string $reason): void
    {
        $this->prestataire->creditWallet((float) $this->amount);

        $this->update([
            'status' => 'rejected',
            'admin_note' => $reason,
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);
    }
}
