<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'opened_by',
        'reason',
        'description',
        'evidences',
        'status',
        'admin_note',
        'resolution',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'evidences' => 'array',
        'resolved_at' => 'datetime',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Résoudre le litige et débloquer la commande en conséquence.
    // FedaPay n'ayant pas d'API de remboursement, refund_client/partial_refund
    // passent la commande en "remboursement à traiter" (cf. Order::refund()) —
    // le remboursement (total ou partiel) reste effectué manuellement par un
    // administrateur, puis confirmé via l'action "Confirmer remboursement".
    public function resolve(string $resolution, int $adminId, ?string $action = null): void
    {
        $this->resolution = $resolution;
        $this->status = 'resolved';
        $this->resolved_at = now();
        $this->resolved_by = $adminId;
        $this->save();

        match ($resolution) {
            'refund_client', 'partial_refund' => $this->order->refund(
                'Litige résolu : ' . ($resolution === 'partial_refund' ? 'remboursement partiel' : 'remboursement client')
            ),
            'pay_prestataire' => $this->order->releasePayment(),
            // "no_action" : la commande n'était pas réellement bloquée (litige
            // non fondé) — elle redevient utilisable normalement.
            default => $this->order->update([
                'status' => $this->order->delivered_at ? 'delivered' : 'in_progress',
            ]),
        };
    }

    // Scope
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
