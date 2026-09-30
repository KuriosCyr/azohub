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
    // Retourne true si l'identifiant a bien été enregistré — false si la demande a été débloquée
    // manuellement (clearAmbiguousFedapayAttempt()) PENDANT que ce virement se préparait (audit
    // externe — 3e audit) : resolveFedapayCustomerId() peut paginer jusqu'à 20 fois avant d'arriver
    // ici, laissant une fenêtre où un admin pressé peut débloquer un essai en réalité toujours en
    // cours (pas mort) et en déclencher un second, réel, en parallèle. PaymentService::
    // initiatePayout() n'appelle sendNow() — l'envoi réel de l'argent — que si ceci renvoie true,
    // pour ne jamais risquer un double paiement sur cette fenêtre.
    public function markFedapayPayoutSent(string $payoutId): bool
    {
        $claimed = DB::transaction(function () use ($payoutId) {
            $record = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$record || $record->fedapay_status !== 'initiating') {
                return false;
            }

            $record->update([
                'fedapay_payout_id' => $payoutId,
                'fedapay_status' => 'pending',
            ]);

            return true;
        });

        // Historique permanent même si l'essai est abandonné ci-dessus, jamais effacé même par
        // clearAmbiguousFedapayAttempt() — voir FedapayPayoutAttempt et PaymentService::
        // processPayoutUpdate() (audit externe).
        FedapayPayoutAttempt::create([
            'withdrawal_request_id' => $this->id,
            'fedapay_payout_id' => $payoutId,
        ]);

        $this->refresh();

        return $claimed;
    }

    public function canRetryFedapayPayout(): bool
    {
        return $this->status === 'pending'
            && $this->fedapay_payout_id === null
            // 'initiating' : une tentative est en train d'être déclenchée par un autre clic /
            // process, entre le verrouillage de la ligne et l'obtention de l'identifiant FedaPay
            // (voir PaymentService::initiatePayout()) — bloque la fenêtre de course sans avoir
            // encore d'identifiant à stocker.
            && $this->fedapay_status !== 'initiating';
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

    // Échec confirmé par FedaPay (webhook) : la demande reste 'pending' — ni payée ni rejetée,
    // l'admin peut réessayer via l'API ou traiter manuellement, sans avoir à en recréer une.
    // NE recrédite PAS le portefeuille : le solde a déjà été débité une seule fois à la création
    // de la demande (PrestataireWallet::requestWithdrawal) et n'a jamais bougé depuis — cet essai
    // raté n'a fait circuler aucun argent, rien à rendre. Un ancien code recréditait ici, ce qui
    // permettait un double paiement (le solde recrédité restait acquis même si un essai suivant,
    // ou "Marquer comme payé", ou "Rejeter" faisait ensuite sortir l'argent une seconde fois).
    public function failFedapayPayout(string $reason): void
    {
        DB::transaction(function () use ($reason) {
            $record = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$record || $record->status !== 'pending' || $record->fedapay_status !== 'pending') {
                return;
            }

            $record->update([
                'fedapay_status' => 'failed',
                'fedapay_payout_id' => null,
                'admin_note' => trim(($record->admin_note ?? '') . "\nÉchec FedaPay : {$reason}"),
            ]);
        });

        $this->refresh();
    }

    // Cas ambigu seulement : PaymentService::initiatePayout() a obtenu un identifiant FedaPay
    // mais n'a jamais pu confirmer si l'envoi a réellement eu lieu (ex. coupure réseau juste
    // après). Tant que ni le webhook FedaPay ni cette action n'ont tranché, les 3 actions
    // normales restent masquées (elles exigent toutes fedapay_payout_id === null) pour ne
    // jamais risquer un double envoi. Un admin qui a VÉRIFIÉ manuellement sur le tableau de
    // bord FedaPay que rien n'a été envoyé peut débloquer la demande avec cette méthode.
    // 'initiating' inclus (audit externe — 2e audit) : si le processus PHP meurt entre la
    // réservation de la demande et l'enregistrement de l'identifiant FedaPay (ex. timeout sur
    // resolveFedapayCustomerId(), qui peut paginer jusqu'à 20 fois), la demande reste bloquée sur
    // 'initiating' sans identifiant — et les 4 actions admin (dont celle-ci, avant ce correctif)
    // étaient toutes masquées, sans aucun moyen de la débloquer. Sans risque de double paiement
    // dans ce cas précis : sendNow() n'a jamais pu être appelé sans identifiant enregistré.
    public function clearAmbiguousFedapayAttempt(): void
    {
        DB::transaction(function () {
            $record = static::whereKey($this->id)->lockForUpdate()->first();

            if (!$record || $record->status !== 'pending') {
                return;
            }

            if ($record->fedapay_payout_id === null && $record->fedapay_status !== 'initiating') {
                return;
            }

            $record->update([
                'fedapay_payout_id' => null,
                'fedapay_status' => null,
            ]);
        });

        $this->refresh();
    }
}
