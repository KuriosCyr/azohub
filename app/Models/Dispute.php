<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'refund_amount',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'evidences' => 'array',
        'refund_amount' => 'decimal:2',
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
    // Verrouillé + gardé par le statut : sans ça, un double clic admin (ou une
    // résolution relancée avec une résolution différente) pourrait déclencher deux
    // branches d'argent contradictoires (ex. paiement prestataire ET remboursement
    // client) sur le même litige.
    // $refundAmount : uniquement pour resolution = 'partial_refund' — montant exact rendu au
    // client, le prestataire recevant automatiquement le reste (cf. Order::partialRefund()).
    public function resolve(string $resolution, int $adminId, ?float $refundAmount = null): void
    {
        DB::transaction(function () use ($resolution, $adminId, $refundAmount) {
            $dispute = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$dispute || $dispute->status === 'resolved') {
                return;
            }

            $dispute->update([
                'resolution' => $resolution,
                'status' => 'resolved',
                'refund_amount' => $resolution === 'partial_refund' ? $refundAmount : null,
                'resolved_at' => now(),
                'resolved_by' => $adminId,
            ]);

            match ($resolution) {
                'refund_client' => $dispute->order->refund('Litige résolu : remboursement client'),
                'partial_refund' => $dispute->order->partialRefund(
                    (float) $refundAmount,
                    'Litige résolu : remboursement partiel'
                ),
                // allowFromDisputed : seul cas légitime où releasePayment() doit accepter une
                // commande encore 'disputed' (voir Order::releasePayment()).
                'pay_prestataire' => $dispute->order->releasePayment(allowFromDisputed: true),
                // "no_action" : la commande n'était pas réellement bloquée (litige
                // non fondé) — elle redevient utilisable normalement.
                default => $dispute->order->reopenAfterDispute(),
            };
        });

        $this->refresh();
    }

    // Scope
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
