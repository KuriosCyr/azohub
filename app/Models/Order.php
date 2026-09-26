<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
        'custom_offer_id',
        'requirements',
        'attachments',
        'first_delivered_at',
        'amount',
        'commission',
        'client_fee',
        'prestataire_amount',
        'delivery_time',
        'expected_delivery_at',
        'deadline_reminded_12h_at',
        'deadline_reminded_1h_at',
        'deadline_overdue_notified_at',
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
        'attachments' => 'array',
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'client_fee' => 'decimal:2',
        'prestataire_amount' => 'decimal:2',
        'delivery_time' => 'integer',
        'deliverables' => 'array',
        'auto_validated' => 'boolean',
        'revision_requested' => 'boolean',
        'expected_delivery_at' => 'datetime',
        'first_delivered_at' => 'datetime',
        'deadline_reminded_12h_at' => 'datetime',
        'deadline_reminded_1h_at' => 'datetime',
        'deadline_overdue_notified_at' => 'datetime',
        'delivered_at' => 'datetime',
        'validated_at' => 'datetime',
        'validation_deadline' => 'datetime',
        'accepted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Taux fixe des frais de service prélevés sur le CLIENT, en plus du prix.
    // Contrairement à la commission prestataire, ce taux ne dépend pas du niveau.
    public const CLIENT_FEE_RATE = 0.05;

    // Les URLs utilisent le numéro de commande plutôt que l'ID brut de la table.
    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    // Titre à afficher quelle que soit l'origine de la commande : service listé,
    // demande négociée (service_request/proposal) ou offre personnalisée en chat direct.
    public function getDisplayTitleAttribute(): string
    {
        return $this->service?->title
            ?? $this->serviceRequest?->title
            ?? $this->customOffer?->title
            ?? 'Commande ' . $this->order_number;
    }

    public function getDisplayCategoryAttribute(): ?string
    {
        return $this->service?->category?->name
            ?? $this->serviceRequest?->category?->name;
    }

    // Montant total réellement débité au client (prix + frais de service client).
    public function getTotalChargedAttribute()
    {
        return round((float) $this->amount + (float) $this->client_fee, 2);
    }

    // Auto-générer le numéro de commande
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                // Valeur temporaire unique le temps que l'ID auto-incrémenté soit
                // connu : max('id')+1 pouvait produire le même numéro pour deux
                // commandes créées au même instant, faisant planter l'une des deux
                // sur la contrainte unique.
                $order->order_number = 'TMP-' . (string) \Illuminate\Support\Str::uuid();
            }
        });

        // Historique des statuts : une ligne à la création, puis une à chaque
        // changement, quel que soit le code qui déclenche le changement (contrôleur,
        // commande planifiée, résolution de litige...).
        static::created(function (Order $order) {
            if (str_starts_with($order->order_number, 'TMP-')) {
                // L'ID auto-incrémenté est unique et définitif : plus de risque de
                // collision, contrairement à un numéro deviné avant l'insertion.
                $order->updateQuietly([
                    'order_number' => 'AZH-' . $order->created_at->format('Y') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                ]);
            }

            $order->statusHistory()->create([
                'status' => $order->status,
                'updated_by' => \Illuminate\Support\Facades\Auth::id(),
            ]);
        });

        static::updated(function (Order $order) {
            if ($order->wasChanged('status')) {
                $order->statusHistory()->create([
                    'status' => $order->status,
                    'updated_by' => \Illuminate\Support\Facades\Auth::id(),
                ]);
            }
        });
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatus::class)->latest();
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

    public function customOffer()
    {
        return $this->belongsTo(CustomOffer::class);
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

    // Helpers de statut
    public function isPendingPayment()
    {
        return $this->status === 'pending_payment';
    }

    // Commande née d'un devis négocié (proposition acceptée) ou d'une offre personnalisée en
    // chat, par opposition à une commande directe sur un service du catalogue. Détermine quand
    // le compte à rebours de livraison démarre : au paiement pour une commande directe, mais
    // seulement quand le prestataire clique "Accepter" pour une commande négociée (le travail
    // n'a pas encore été cadré avant l'acceptation, contrairement à un service déjà défini).
    public function isNegotiated(): bool
    {
        return $this->proposal_id !== null || $this->custom_offer_id !== null;
    }

    // Compte à rebours de livraison
    public function isDeliveryOverdue(): bool
    {
        return $this->expected_delivery_at !== null
            && $this->expected_delivery_at->isPast()
            && in_array($this->status, ['in_progress'], true);
    }

    // Marge de tolérance avant de considérer une livraison "en retard" pour le calcul de
    // ponctualité (User::updatePunctuality()) : évite de sanctionner un dépôt à quelques
    // minutes/heures près pour un motif purement horaire.
    public const PUNCTUALITY_GRACE_HOURS = 3;

    // Ponctualité de la PREMIÈRE livraison (first_delivered_at, jamais écrasé par une
    // re-livraison après révision) comparée au délai annoncé. Null tant que la commande n'a
    // jamais été livrée, ou qu'aucun délai n'était attendu (offre personnalisée sans délai
    // par ex.) : dans ce cas elle ne doit compter ni pour ni contre le prestataire.
    public function wasDeliveredOnTime(): ?bool
    {
        if ($this->first_delivered_at === null || $this->expected_delivery_at === null) {
            return null;
        }

        return $this->first_delivered_at->lessThanOrEqualTo(
            $this->expected_delivery_at->copy()->addHours(self::PUNCTUALITY_GRACE_HOURS)
        );
    }

    // Un litige peut être ouvert par le client ou le prestataire tant que la
    // commande est en cours (prestataire silencieux) ou livrée (client pas
    // satisfait, au-delà d'une simple demande de révision) — et seulement si
    // aucun litige n'existe déjà dessus.
    public function canOpenDispute()
    {
        return in_array($this->status, ['in_progress', 'delivered']) && !$this->dispute;
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

    // Libérer le paiement au prestataire (validation client, auto-validation après
    // délai, ou litige tranché en sa faveur). Verrouillée en transaction et gardée
    // par un contrôle de payment_status pour éviter un double crédit du portefeuille
    // si deux déclencheurs (client + cron d'auto-validation) se chevauchent.
    public function releasePayment(bool $autoValidated = false)
    {
        DB::transaction(function () use ($autoValidated) {
            $order = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$order || $order->payment_status === 'released') {
                return;
            }

            $updateData = [
                'status' => 'completed',
                'payment_status' => 'released',
                'validated_at' => $order->validated_at ?? now(),
            ];

            if ($autoValidated) {
                $updateData['auto_validated'] = true;
            }

            $order->update($updateData);

            if ($order->prestataire) {
                $order->prestataire->increment('wallet_balance', $order->prestataire_amount);
                $order->prestataire->increment('completed_orders');
                $order->prestataire->refresh()->updateLevel();
            }

            $order->service?->increment('total_orders');
        });

        $this->refresh();
    }

    // Helper pour obtenir le libellé du statut
    public function getStatusLabelAttribute()
    {
        return self::statusLabel($this->status);
    }

    public static function statusLabel(string $status): string
    {
        return match($status) {
            'pending_payment' => 'En attente de paiement',
            'paid' => 'Payée',
            'accepted' => 'Acceptée',
            'in_progress' => 'En cours',
            'delivered' => 'Livrée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            'refused' => 'Refusée',
            'disputed' => 'Litige',
            'refund_pending' => 'Remboursement en cours',
            'refunded' => 'Remboursée',
            default => ucfirst($status),
        };
    }

    // Couleur du badge associé au statut — un seul endroit à tenir à jour, utilisé
    // partout où un statut de commande s'affiche (dashboard client, liste prestataire...).
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending_payment' => 'bg-ochre-500/15 text-ink-900',
            'paid' => 'bg-clay-500/15 text-ink-900',
            'accepted', 'in_progress' => 'bg-clay-500/15 text-ink-900',
            'delivered' => 'bg-ochre-500/30 text-ink-900',
            'completed' => 'bg-forest-600/10 text-forest-700',
            'cancelled', 'refused' => 'bg-red-100 text-red-800',
            'disputed' => 'bg-red-100 text-red-800',
            'refund_pending' => 'bg-ochre-500/15 text-ink-900',
            'refunded' => 'bg-ink-100 text-ink-700',
            default => 'bg-ink-100 text-ink-700',
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