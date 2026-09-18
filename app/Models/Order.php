<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'client_id',
        'prestataire_id',
        'service_id',
        'service_request_id',
        'proposal_id',
        'requirements',
        'amount',
        'commission',
        'prestataire_amount',
        'delivery_time',
        'expected_delivery_at',
        'delivered_at',
        'status',
        'payment_status',
        'deliverables',
        'delivery_note',
        'validation_deadline',
        'validated_at',
        'auto_validated',
        'accepted_at',
        'cancelled_at',
        'cancellation_reason',
        'revision_requested',
        'revision_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'prestataire_amount' => 'decimal:2',
        'delivery_time' => 'integer',
        'deliverables' => 'array',
        'auto_validated' => 'boolean',
        'revision_requested' => 'boolean',
        'expected_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'validated_at' => 'datetime',
        'validation_deadline' => 'datetime',
        'accepted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Auto-générer le numéro de commande
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'AZH-' . date('Y') . '-' . str_pad(self::max('id') + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    // Accesseurs pour compatibilité avec le code existant
    public function getTotalPriceAttribute()
    {
        return $this->amount;
    }

    public function setTotalPriceAttribute($value)
    {
        $this->attributes['amount'] = $value;
    }

    // Relations
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function prestataire()
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Relation review au SINGULIER (une commande = un avis)
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Relation reviews au PLURIEL pour compatibilité
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function dispute()
    {
        return $this->hasOne(Dispute::class);
    }

    // Scopes
    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pending_payment');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Helpers de statut
    public function isPendingPayment()
    {
        return $this->status === 'pending_payment';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isDisputed()
    {
        return $this->status === 'disputed';
    }

    // Helpers de permission
    public function canBeAccepted()
    {
        return $this->status === 'paid';
    }

    public function canBeRefused()
    {
        return $this->status === 'paid';
    }

    public function canBeDelivered()
    {
        return $this->status === 'in_progress';
    }

    public function canBeValidated()
    {
        return $this->status === 'delivered';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending_payment', 'paid']);
    }

    public function canRequestRevision()
    {
        return $this->status === 'delivered';
    }

    // Rembourser le client (annulation ou litige tranché en sa faveur)
    // FedaPay n'expose pas d'API de remboursement automatique : un paiement déjà
    // encaissé passe en "refund_pending" et doit être traité manuellement par un
    // administrateur depuis le dashboard FedaPay, puis confirmé côté Azohub via
    // Order::confirmRefund().
    public function refund(?string $reason = null)
    {
        $successfulPayment = $this->payments()->where('status', 'success')->latest()->first();

        $this->update([
            'status' => 'cancelled',
            'payment_status' => $successfulPayment ? 'refund_pending' : $this->payment_status,
            'cancelled_at' => $this->cancelled_at ?? now(),
            'cancellation_reason' => $reason ?? $this->cancellation_reason,
        ]);

        $successfulPayment?->update(['status' => 'refund_pending']);
    }

    // Confirme qu'un remboursement en attente a bien été traité manuellement
    // (bouton admin, une fois le remboursement effectué depuis le dashboard FedaPay).
    public function confirmRefund(): void
    {
        $this->update(['payment_status' => 'refunded']);

        $this->payments()->where('status', 'refund_pending')->update(['status' => 'refunded']);
    }

    // Libérer le paiement au prestataire (validation client ou litige tranché en sa faveur)
    public function releasePayment()
    {
        $this->update([
            'status' => 'completed',
            'payment_status' => 'released',
            'validated_at' => $this->validated_at ?? now(),
        ]);

        if ($this->prestataire) {
            $this->prestataire->increment('wallet_balance', $this->prestataire_amount);
            $this->prestataire->increment('completed_orders');
            $this->prestataire->refresh()->updateLevel();
        }

        $this->service?->increment('total_orders');
    }

    // Helper pour obtenir le libellé du statut
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending_payment' => 'En attente de paiement',
            'paid' => 'Payée',
            'in_progress' => 'En cours',
            'delivered' => 'Livrée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            'disputed' => 'Litige',
            default => ucfirst($this->status),
        };
    }

    // Helper pour le statut de paiement
    public function getPaymentStatusLabelAttribute()
    {
        return match($this->payment_status) {
            'pending' => 'En attente',
            'held' => 'Bloqué (Escrow)',
            'released' => 'Libéré',
            'refund_pending' => 'Remboursement en cours',
            'refunded' => 'Remboursé',
            default => ucfirst($this->payment_status),
        };
    }
}