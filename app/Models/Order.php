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
        if (!$useReferralCredit) {
            return (float) $this->referral_credit_applied;
        }

        // Verrouillée EN PREMIER, avant tout débit (audit externe — 5e audit) : l'ancienne version
        // testait promo_code_id/referral_credit_applied sur $this (objet en mémoire, jamais
        // rechargé) avant de débiter le solde client, et ne revérifiait que le statut sous verrou.
        // Deux requêtes concurrentes (double clic, deux onglets) sur la même commande passaient
        // alors TOUTES LES DEUX le contrôle initial, débitaient chacune le solde client
        // (redeemReferralCredit() est verrouillé côté User, donc réellement débité à chaque fois
        // si le solde le permet), puis écrivaient chacune sur la commande l'une après l'autre : la
        // seconde écrasait la valeur de la première SANS déclencher de remboursement pour son
        // débit — un crédit orphelin, perdu pour toujours. Verrouiller la commande d'abord et
        // revérifier TOUS les champs déjà testés avant le verrou élimine cette fenêtre : le solde
        // n'est plus jamais débité pour un crédit qui ne sera pas réellement appliqué.
        return DB::transaction(function () {
            $order = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$order || $order->status !== 'pending_payment') {
                return (float) $this->referral_credit_applied;
            }

            if ($order->promo_code_id || (float) $order->referral_credit_applied > 0) {
                return (float) $order->referral_credit_applied;
            }

            $redeemableCap = max(0, (float) $order->total_charged - 1);
            $redeemed = $order->client->redeemReferralCredit($redeemableCap, $order);

            if ($redeemed > 0) {
                $order->update(['referral_credit_applied' => $redeemed]);
                $this->refresh();
            }

            return $redeemed;
        });
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

        return DB::transaction(function () use ($rawCode) {
            // Verrouillée + TOUS les champs déjà testés avant le verrou revérifiés dessus (audit
            // externe — 4e puis 5e audit) : promo_code_id et referral_credit_applied n'étaient
            // auparavant lus que sur $this (objet en mémoire, jamais rechargé) avant le verrou —
            // seul order.status était revérifié sous verrou. Une commande annulée entre-temps (ex.
            // expiration automatique) ne doit plus se voir appliquer un code promo après coup, ce
            // qui consommerait inutilement un usage du code (et bloquerait ce client de le
            // réutiliser, alreadyUsedBy() ci-dessous) sans que la commande ne soit jamais payée —
            // et l'exclusion mutuelle avec le crédit de parrainage doit tenir même si les deux
            // méthodes sont appelées concurremment sur la même commande.
            $order = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$order || $order->status !== 'pending_payment') {
                return "Cette commande n'est plus en attente de paiement.";
            }

            if ($order->promo_code_id) {
                return null;
            }

            if ((float) $order->referral_credit_applied > 0) {
                return 'Le crédit de parrainage est déjà appliqué à cette commande.';
            }

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

            if ($code->alreadyUsedBy($order->client_id)) {
                return 'Vous avez déjà utilisé ce code promo.';
            }

            if (!$code->meetsMinimumOrder((float) $order->total_charged)) {
                return "Cette commande n'atteint pas le montant minimum requis pour ce code promo.";
            }

            // Plafonne la réduction à la marge d'Azohub sur cette commande (commission + frais
            // client) : elle ne peut jamais dépasser ce qu'Azohub a réellement encaissé, pour ne
            // jamais faire perdre d'argent à la plateforme sur une commande (audit externe).
            $discount = $code->discountFor(
                (float) $order->total_charged,
                (float) $order->commission + (float) $order->client_fee
            );

            if ($discount <= 0) {
                return "Ce code promo ne peut pas s'appliquer à cette commande.";
            }

            $order->update([
                'promo_code_id' => $code->id,
                'promo_discount_applied' => $discount,
            ]);

            PromoCodeRedemption::create([
                'promo_code_id' => $code->id,
                'user_id' => $order->client_id,
                'order_id' => $order->id,
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
    // Payment::confirmRefund() (sur la fiche du paiement, pas de la commande — audit
    // externe, 3e audit).
    // Retourne true si l'annulation a réellement eu lieu, false si l'appel n'a rien fait (déjà
    // traité autrement) — audit externe (2e audit) : sans ça, Dispute::resolve() marquait le
    // litige "résolu : remboursement client" même quand refund() n'avait en réalité rien changé.
    // $mustBeStaleSince : réservé à ExpireStalePendingPayments (audit externe — 3e audit) — sans
    // ça, une commande chargée "stale" AVANT le verrou, mais touch()ée entre-temps par une
    // nouvelle tentative de paiement concurrente (PaymentService::initiateForOrder()), pouvait
    // quand même être annulée sous le pied du client une fois le verrou pris ici. Sans perte
    // réelle dans ce cas précis (l'argent confirmé ensuite finit en refund_pending, traçable), mais
    // une commande activement en train d'être payée ne doit plus être annulée par erreur.
    public function refund(?string $reason = null, ?\Illuminate\Support\Carbon $mustBeStaleSince = null): bool
    {
        $refunded = DB::transaction(function () use ($reason, $mustBeStaleSince) {
            // Verrouillée + revérifiée sous verrou (audit externe) : sans ça, un remboursement
            // demandé au même instant qu'une libération de paiement concurrente (releasePayment())
            // pouvait s'exécuter quand même après coup, écrasant payment_status='released' en
            // 'refund_pending' sans que rien ne signale que le prestataire avait déjà été payé —
            // les deux parties se retrouvaient payées sur la même commande.
            // withTrashed() (audit externe — 3e audit) : une commande pending_payment supprimée
            // par un admin restait invisible à cette requête (contrainte par le global scope
            // SoftDeletes), donc jamais annulée par ExpireStalePendingPayments — le crédit de
            // parrainage ou le code promo éventuellement consommé dessus restait bloqué pour
            // toujours.
            $order = static::withTrashed()->whereKey($this->id)->lockForUpdate()->first();

            if (!$order) {
                return false;
            }

            if ($mustBeStaleSince && $order->updated_at->gt($mustBeStaleSince)) {
                return false;
            }

            if (in_array($order->payment_status, ['released', 'refund_pending', 'refunded'], true)) {
                if ($order->payment_status === 'released') {
                    AdminNotifier::actionRequired(
                        'Remboursement demandé sur une commande déjà payée au prestataire',
                        "La commande {$order->order_number} devait être remboursée au client, mais le prestataire a déjà été payé (payment_status=released). Décision manuelle nécessaire.",
                        route('filament.admin.resources.orders.edit', $order),
                    );
                }

                return false;
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
                $order->client->refundReferralCredit($creditToRestore, $order);
            }

            if ($releasePromoCode) {
                PromoCodeRedemption::where('order_id', $order->id)->delete();
            }

            $successfulPayment?->update([
                'status' => 'refund_pending',
                'refund_amount_due' => (float) $successfulPayment->amount,
            ]);

            return true;
        });

        $this->refresh();

        return $refunded;
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

            // Filet de sécurité en plus de la validation du formulaire admin (audit externe) :
            // ne jamais rembourser plus que ce que le client a réellement payé (total_charged),
            // même si cette méthode est appelée autrement qu'via ce formulaire.
            $clientRefundAmount = min($clientRefundAmount, (float) $order->total_charged);

            // Proportion calculée sur total_charged (ce qu'Azohub a réellement encaissé), pas sur
            // amount (le prix affiché) — audit externe : avec une réduction (promo ou crédit de
            // parrainage) appliquée, baser le ratio sur amount faisait payer au prestataire une
            // part calculée sur un montant plus gros que ce qui avait été perçu, et Azohub
            // reversait alors plus que ce qu'il avait encaissé (perte nette sur la commande).
            // Un remboursement total (ratio = 1) laisse maintenant bien le prestataire à 0.
            $totalCharged = (float) $order->total_charged;
            $refundRatio = $totalCharged > 0 ? min(1, max(0, $clientRefundAmount / $totalCharged)) : 1;
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

            // $clientRefundAmount et non le montant total payé (audit externe) : un remboursement
            // partiel ne doit pas apparaître comme "à rembourser en entier" sur le tableau de
            // bord financier (FinancialOverview) — le reste est déjà chez le prestataire.
            $successfulPayment = $order->payments()->where('status', 'success')->latest()->first();
            $successfulPayment?->update([
                'status' => 'refund_pending',
                'refund_amount_due' => $clientRefundAmount,
            ]);

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
    // Retourne true si le paiement a réellement été libéré, false si l'appel n'a rien fait (déjà
    // traité, ou commande en litige) — audit externe (2e audit) : les deux appelants
    // (OrderController::validate(), ValidateExpiredOrders) envoyaient auparavant une notification
    // "paiement reçu" au prestataire sans jamais vérifier si quoi que ce soit avait vraiment eu
    // lieu, ce qui pouvait envoyer un message trompeur si un litige s'était ouvert entre-temps.
    public function releasePayment(bool $autoValidated = false, bool $allowFromDisputed = false): bool
    {
        $released = DB::transaction(function () use ($autoValidated, $allowFromDisputed) {
            // withTrashed() (audit externe — 4e audit) : comme refund()/markAsPaid(), une commande
            // en litige peut être soft-deleted par un admin — Dispute::resolve() ('pay_prestataire')
            // ne doit pas échouer silencieusement dans ce cas, le prestataire doit quand même être
            // payé pour un travail réellement livré.
            $order = static::withTrashed()->whereKey($this->id)->lockForUpdate()->first();

            // Une commande déjà libérée, déjà en attente de remboursement ou déjà remboursée ne
            // doit plus jamais repasser par ici (audit externe : releasePayment() ne bloquait
            // que 'released', ce qui pouvait payer le prestataire sur une commande déjà marquée
            // à rembourser au client — un litige tranché en cours de traitement, par exemple).
            if (!$order || in_array($order->payment_status, ['released', 'refund_pending', 'refunded'], true)) {
                return false;
            }

            // Le statut doit être exactement celui attendu au moment de l'exécution, sous verrou
            // (audit externe — 3e audit) : validate() vérifie 'delivered' AVANT de prendre ce
            // verrou — une demande de révision concurrente (elle-même verrouillée, cf.
            // OrderController::requestRevision()) peut faire passer la commande en 'in_progress'
            // entre-temps. L'ancien contrôle ne bloquait que 'disputed' : une commande remise en
            // travail par une révision pouvait quand même voir son paiement libéré juste après.
            // $allowFromDisputed est réservé à Dispute::resolve() ('pay_prestataire'), seul cas
            // légitime où la commande est encore 'disputed' à cet instant.
            $expectedStatus = $allowFromDisputed ? 'disputed' : 'delivered';

            if ($order->status !== $expectedStatus) {
                return false;
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

            return true;
        });

        $this->refresh();

        return $released;
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