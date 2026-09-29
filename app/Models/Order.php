<?php

namespace App\Models;

use App\Services\AdminNotifier;
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
        'service_package_id',
        'service_request_id',
        'proposal_id',
        'custom_offer_id',
        'requirements',
        'attachments',
        'first_delivered_at',
        'amount',
        'commission',
        'client_fee',
        'referral_credit_applied',
        'promo_code_id',
        'promo_discount_applied',
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
        'revisions_included',
        'revisions_used',
    ];

    protected $casts = [
        'attachments' => 'array',
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'client_fee' => 'decimal:2',
        'referral_credit_applied' => 'decimal:2',
        'promo_discount_applied' => 'decimal:2',
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
        'revisions_included' => 'integer',
        'revisions_used' => 'integer',
    ];

    // Taux fixe des frais de service prélevés sur le CLIENT, en plus du prix.
    // Contrairement à la commission prestataire, ce taux ne dépend pas du niveau.
    public const CLIENT_FEE_RATE = 0.05;

    // Nombre de révisions par défaut quand la commande ne vient pas d'un service (proposition
    // sur une demande client ouverte) ou d'une offre personnalisée sans valeur explicite.
    public const DEFAULT_REVISIONS_INCLUDED = 2;

    // Le client peut encore demander une révision : la commande est livrée, et le quota fixé
    // à la création de la commande n'est pas épuisé.
    public function canRequestRevision(): bool
    {
        return $this->status === 'delivered' && $this->revisions_used < $this->revisions_included;
    }

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

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    // Montant total réellement débité au client (prix + frais de service client, réduit du
    // crédit de parrainage ou du code promo éventuellement appliqué — les deux sont
    // mutuellement exclusifs, voir applyReferralCredit()/applyPromoCode()).
    public function getTotalChargedAttribute()
    {
        return round(
            (float) $this->amount + (float) $this->client_fee
            - (float) $this->referral_credit_applied - (float) $this->promo_discount_applied,
            2
        );
    }

    // Consomme le crédit de parrainage du client sur cette commande, si demandé et pas déjà
    // fait (idempotent : une commande "pending_payment" reprise après un paiement abandonné ne
    // consomme le crédit qu'à la première tentative), et jamais si un code promo est déjà
    // appliqué (mutuellement exclusifs). Toujours appelé avant de construire la transaction
    // FedaPay dans PaymentService::initiateForOrder() — un montant nul ou négatif y serait
    // rejeté, donc au moins 1 FCFA reste toujours à payer même si le crédit disponible
    // suffirait à tout couvrir. Retourne le montant effectivement appliqué.
    public function applyReferralCredit(bool $useReferralCredit): float
    {
        if (!$useReferralCredit || $this->promo_code_id || (float) $this->referral_credit_applied > 0) {
            return (float) $this->referral_credit_applied;
        }

        $redeemableCap = max(0, (float) $this->total_charged - 1);
        $redeemed = $this->client->redeemReferralCredit($redeemableCap);

        if ($redeemed > 0) {
            $this->update(['referral_credit_applied' => $redeemed]);
            $this->refresh();
        }

        return $redeemed;
    }

    // Applique un code promo à cette commande, si fourni et pas déjà fait (idempotent, comme
    // applyReferralCredit() — voir OrderCreate/ProposalAccept/CustomOfferAccept::placeOrder()).
    // Retourne un message d'erreur lisible si le code est invalide, épuisé, déjà utilisé par ce
    // client, ou qu'un crédit de parrainage est déjà appliqué — null si tout s'est bien passé
    // (ou si aucun code n'était fourni).
    public function applyPromoCode(?string $rawCode): ?string
    {
        if (!$rawCode) {
            return null;
        }

        if ($this->promo_code_id) {
            return null;
        }

        if ((float) $this->referral_credit_applied > 0) {
            return 'Le crédit de parrainage est déjà appliqué à cette commande.';
        }

        return DB::transaction(function () use ($rawCode) {
            // Verrouille la ligne du code : deux tentatives concurrentes sur le dernier usage
            // disponible d'un code à max_uses limité se sérialisent ici, la seconde ne comptant
            // le nombre d'utilisations qu'une fois la première validée (voir maybeRewardReferrer()
            // pour le même principe sur le plafond de parrainages récompensés).
            $code = PromoCode::whereRaw('UPPER(code) = ?', [strtoupper(trim($rawCode))])
                ->lockForUpdate()
                ->first();

            if (!$code || !$code->is_active) {
                return "Ce code promo n'existe pas ou n'est plus actif.";
            }

            if ($code->isExpired()) {
                return 'Ce code promo a expiré.';
            }

            if ($code->hasReachedMaxUses()) {
                return "Ce code promo a atteint son nombre maximum d'utilisations.";
            }

            if ($code->alreadyUsedBy($this->client_id)) {
                return 'Vous avez déjà utilisé ce code promo.';
            }

            if (!$code->meetsMinimumOrder((float) $this->total_charged)) {
                return "Cette commande n'atteint pas le montant minimum requis pour ce code promo.";
            }

            // Plafonne la réduction à la marge d'Azohub sur cette commande (commission + frais
            // client) : elle ne peut jamais dépasser ce qu'Azohub a réellement encaissé, pour ne
            // jamais faire perdre d'argent à la plateforme sur une commande (audit externe).
            $discount = $code->discountFor(
                (float) $this->total_charged,
                (float) $this->commission + (float) $this->client_fee
            );

            if ($discount <= 0) {
                return "Ce code promo ne peut pas s'appliquer à cette commande.";
            }

            $this->update([
                'promo_code_id' => $code->id,
                'promo_discount_applied' => $discount,
            ]);

            PromoCodeRedemption::create([
                'promo_code_id' => $code->id,
                'user_id' => $this->client_id,
                'order_id' => $this->id,
                'amount_applied' => $discount,
            ]);

            $this->refresh();

            return null;
        });
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

    public function servicePackage()
    {
        return $this->belongsTo(ServicePackage::class);
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
        DB::transaction(function () use ($reason) {
            // Verrouillée + revérifiée sous verrou (audit externe) : sans ça, un remboursement
            // demandé au même instant qu'une libération de paiement concurrente (releasePayment())
            // pouvait s'exécuter quand même après coup, écrasant payment_status='released' en
            // 'refund_pending' sans que rien ne signale que le prestataire avait déjà été payé —
            // les deux parties se retrouvaient payées sur la même commande.
            $order = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$order) {
                return;
            }

            if (in_array($order->payment_status, ['released', 'refund_pending', 'refunded'], true)) {
                if ($order->payment_status === 'released') {
                    AdminNotifier::actionRequired(
                        'Remboursement demandé sur une commande déjà payée au prestataire',
                        "La commande {$order->order_number} devait être remboursée au client, mais le prestataire a déjà été payé (payment_status=released). Décision manuelle nécessaire.",
                        route('filament.admin.resources.orders.edit', $order),
                    );
                }

                return;
            }

            $successfulPayment = $order->payments()->where('status', 'success')->latest()->first();

            // Un paiement réussi a déjà réellement débité le client pour le montant réduit : le
            // crédit consommé a bien servi à payer cette commande (remboursée séparément comme
            // le reste du paiement, cf. confirmRefund()). Seule une commande jamais payée
            // restitue son crédit — sans ça, l'argent-crédit disparaîtrait sans avoir payé quoi
            // que ce soit. (float) plutôt qu'une comparaison directe : Order::create() ne relit
            // pas la ligne insérée, donc une commande créée sans ce champ explicite garde `null`
            // en mémoire même si la colonne vaut bien 0 en base — un `null` brut réécrit ensuite
            // ferait échouer la contrainte NOT NULL.
            $creditToRestore = (!$successfulPayment && (float) $order->referral_credit_applied > 0)
                ? (float) $order->referral_credit_applied
                : 0;

            // Même principe que le crédit de parrainage : un code promo consommé par une
            // commande jamais payée n'a servi à rien, on le rend disponible (pour ce même client
            // comme pour le plafond global) en supprimant sa trace d'utilisation.
            $releasePromoCode = !$successfulPayment && $order->promo_code_id;

            $order->update([
                'status' => 'cancelled',
                'payment_status' => $successfulPayment ? 'refund_pending' : $order->payment_status,
                'cancelled_at' => $order->cancelled_at ?? now(),
                'cancellation_reason' => $reason ?? $order->cancellation_reason,
                'referral_credit_applied' => $creditToRestore > 0 ? 0 : (float) $order->referral_credit_applied,
                'promo_code_id' => $releasePromoCode ? null : $order->promo_code_id,
                'promo_discount_applied' => $releasePromoCode ? 0 : (float) $order->promo_discount_applied,
            ]);

            if ($creditToRestore > 0) {
                $order->client->refundReferralCredit($creditToRestore);
            }

            if ($releasePromoCode) {
                PromoCodeRedemption::where('order_id', $order->id)->delete();
            }

            $successfulPayment?->update(['status' => 'refund_pending']);
        });

        $this->refresh();
    }

    // Confirme qu'un remboursement en attente a bien été traité manuellement
    // (bouton admin, une fois le remboursement effectué depuis le dashboard FedaPay).
    public function confirmRefund(): void
    {
        $this->update(['payment_status' => 'refunded']);

        $this->payments()->where('status', 'refund_pending')->update(['status' => 'refunded']);
    }

    // Litige tranché "aucune action" (non fondé) : la commande retourne à l'état qu'elle avait
    // avant l'ouverture du litige. Redonne un délai de validation complet si elle retourne en
    // 'delivered' — sans ça, l'ancien délai (déjà expiré ou presque, le litige ayant pris du
    // temps à traiter) laissait l'auto-validation se déclencher dans l'heure suivante au lieu de
    // laisser au client le temps normal de vérifier la livraison (audit externe).
    public function reopenAfterDispute(): void
    {
        DB::transaction(function () {
            $order = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$order) {
                return;
            }

            $order->update([
                'status' => $order->delivered_at ? 'delivered' : 'in_progress',
                'validation_deadline' => $order->delivered_at ? now()->addHours(72) : $order->validation_deadline,
            ]);
        });

        $this->refresh();
    }

    // Litige tranché "à moitié" : le client récupère $clientRefundAmount (à traiter
    // manuellement, comme refund()) et le prestataire reçoit tout de suite le reste de sa part,
    // réduite dans la même proportion que le remboursement client (ex. un remboursement de 50%
    // du prix laisse le prestataire avec 50% de ce qu'il aurait touché en cas de livraison
    // complète). Verrouillée comme releasePayment() : sans ça, une résolution relancée
    // créditerait deux fois le portefeuille du prestataire.
    public function partialRefund(float $clientRefundAmount, ?string $reason = null): void
    {
        DB::transaction(function () use ($clientRefundAmount, $reason) {
            $order = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$order || in_array($order->payment_status, ['released', 'refunded'], true)) {
                return;
            }

            $refundRatio = $order->amount > 0 ? min(1, max(0, $clientRefundAmount / (float) $order->amount)) : 1;
            $prestatairePayout = round((float) $order->prestataire_amount * (1 - $refundRatio), 2);

            $order->update([
                'status' => 'completed',
                'payment_status' => 'refund_pending',
                'validated_at' => $order->validated_at ?? now(),
                'cancellation_reason' => $reason ?? $order->cancellation_reason,
            ]);

            if ($order->prestataire && $prestatairePayout > 0) {
                $order->prestataire->creditWallet(
                    $prestatairePayout,
                    'Litige résolu (remboursement partiel) — commande ' . $order->order_number,
                    $order
                );
            }

            $successfulPayment = $order->payments()->where('status', 'success')->latest()->first();
            $successfulPayment?->update(['status' => 'refund_pending']);

            // Pas d'incrément de completed_orders/total_orders ici (contrairement à
            // releasePayment()) : une commande soldée par un litige ne doit pas gonfler
            // artificiellement la réputation du prestataire comme une livraison réussie.
        });

        $this->refresh();
    }

    // Libérer le paiement au prestataire (validation client, auto-validation après
    // délai, ou litige tranché en sa faveur). Verrouillée en transaction et gardée
    // par un contrôle de payment_status pour éviter un double crédit du portefeuille
    // si deux déclencheurs (client + cron d'auto-validation) se chevauchent.
    // $allowFromDisputed : réservé à Dispute::resolve() ('pay_prestataire') — seul cas légitime
    // où la commande est encore 'disputed' au moment de l'appel.
    public function releasePayment(bool $autoValidated = false, bool $allowFromDisputed = false)
    {
        DB::transaction(function () use ($autoValidated, $allowFromDisputed) {
            $order = static::whereKey($this->id)->lockForUpdate()->first();

            // Une commande déjà libérée, déjà en attente de remboursement ou déjà remboursée ne
            // doit plus jamais repasser par ici (audit externe : releasePayment() ne bloquait
            // que 'released', ce qui pouvait payer le prestataire sur une commande déjà marquée
            // à rembourser au client — un litige tranché en cours de traitement, par exemple).
            if (!$order || in_array($order->payment_status, ['released', 'refund_pending', 'refunded'], true)) {
                return;
            }

            // Un litige ouvert entre-temps (DisputeController::store(), verrouillée comme ici)
            // ne doit jamais être court-circuité par une validation client ou l'auto-validation
            // qui aurait démarré juste avant — seule une résolution de litige explicite peut
            // libérer le paiement d'une commande encore 'disputed' (audit externe : sans ce
            // garde-fou, une commande pouvait finir 'disputed' ET payment_status='released' en
            // même temps).
            if (!$allowFromDisputed && $order->status === 'disputed') {
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
                $order->prestataire->creditWallet(
                    (float) $order->prestataire_amount,
                    'Paiement libéré — commande ' . $order->order_number,
                    $order
                );
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