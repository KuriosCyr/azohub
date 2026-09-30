<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderCreate extends Component
{
    use WithFileUploads;

    public Service $service;
    public string $package = 'basic';
    public string $requirements = '';
    public string $paymentMethod = 'mtn_momo';
    public bool $useReferralCredit = false;
    public string $promoCode = '';
    public $attachments = [];

    // La formule choisie sur la page du service (?package=), résolue une seule fois : si le
    // service ne propose pas de formules, ou si le tier demandé n'existe pas/plus, retombe sur
    // le prix unique du service (jamais d'erreur pour l'utilisateur dans ce cas).
    public function getResolvedPackageProperty(): ?ServicePackage
    {
        if (!$this->service->hasPackages()) {
            return null;
        }

        return $this->service->packages->firstWhere('tier', $this->package)
            ?? $this->service->packages->first();
    }

    public function mount()
    {
        $serviceId = request()->query('service');

        abort_if(!$serviceId, 404);

        $this->service = Service::with(['prestataire', 'category', 'packages'])->findOrFail($serviceId);
        $this->package = request()->query('package', 'basic');

        // Vérifier que le service est encore commandable
        if (!$this->service->isOrderable()) {
            return redirect()->route('services.index')
                ->with('error', 'Ce service n\'est plus disponible.');
        }

        // Vérifier que l'utilisateur n'est pas le prestataire
        if (auth()->id() === $this->service->user_id) {
            return redirect()->route('services.show', $this->service)
                ->with('error', 'Vous ne pouvez pas commander votre propre service.');
        }
    }

    public function placeOrder(PaymentService $payments)
    {
        // Le composant peut rester ouvert longtemps : on revérifie au moment de commander.
        abort_unless($this->service->fresh()?->isOrderable(), 403, "Ce service n'est plus disponible.");

        $this->validate([
            'requirements' => 'required|string|min:20|max:2000',
            'paymentMethod' => 'required|in:mtn_momo,moov_money,celtiis_cash,card',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,rar,mp4,mp3,mov',
        ], [
            'requirements.required' => 'Veuillez décrire vos besoins.',
            'requirements.min' => 'La description doit faire au moins 20 caractères.',
            'requirements.max' => 'La description ne peut pas dépasser 2000 caractères.',
            'attachments.*.max' => 'Chaque fichier ne peut pas dépasser 10 MB.',
            'attachments.*.mimes' => 'Type de fichier non autorisé.',
        ]);

        $uploadedAttachments = [];
        foreach ($this->attachments as $file) {
            $path = $file->store('orders', 'local');
            $uploadedAttachments[] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
                'uploaded_at' => now()->toDateTimeString(),
            ];
        }

        // Une tentative précédente abandonnée (ex. code promo invalide) pour ce même service est
        // reprise plutôt que d'en créer une nouvelle (voir plus bas) — cherchée dès maintenant
        // pour savoir, avant d'écraser quoi que ce soit, s'il y a d'anciens fichiers à conserver
        // ou à effacer du disque (audit externe : sans ça, un nouvel envoi de fichiers sur un
        // nouvel essai laissait les précédents orphelins sur le disque, plus référencés nulle
        // part).
        $existingOrder = Order::where('client_id', auth()->id())
            ->where('service_id', $this->service->id)
            ->where('service_package_id', $this->resolvedPackage?->id)
            ->where('status', 'pending_payment')
            ->first();

        if (!empty($uploadedAttachments)) {
            // De nouveaux fichiers remplacent les précédents : ceux-ci ne sont plus référencés
            // nulle part, on les efface du disque avant d'écrire les nouveaux.
            foreach ($existingOrder?->attachments ?? [] as $old) {
                Storage::disk('local')->delete($old['path']);
            }
        } elseif ($existingOrder) {
            // Aucun nouveau fichier sélectionné sur cette tentative : conserve ceux déjà
            // envoyés lors de la précédente, au lieu de les effacer de la commande (ils
            // restent utilisables sur le disque).
            $uploadedAttachments = $existingOrder->attachments ?? [];
        }

        $pkg = $this->resolvedPackage;
        $price = $pkg?->price ?? $this->service->price;
        $deliveryTime = $pkg?->delivery_time ?? $this->service->delivery_time;
        $revisionsIncluded = $pkg?->revisions_included ?? $this->service->revisions_included;

        $commission = round($price * $this->service->prestataire->commissionRate(), 2);
        $clientFee = round($price * Order::CLIENT_FEE_RATE, 2);

        $orderData = [
            'client_id'        => auth()->id(),
            'prestataire_id'   => $this->service->user_id,
            'service_id'       => $this->service->id,
            'service_package_id' => $pkg?->id,
            'requirements'     => $this->requirements,
            'attachments'      => !empty($uploadedAttachments) ? $uploadedAttachments : null,
            'amount'           => $price,
            'commission'       => $commission,
            'client_fee'       => $clientFee,
            'prestataire_amount' => $price - $commission,
            'delivery_time'    => $deliveryTime,
            'revisions_included' => $revisionsIncluded,
            'status'           => 'pending_payment',
            'payment_status'   => 'pending',
        ];

        // Une tentative précédente abandonnée pour ce même service (ex. code promo invalide,
        // paiement jamais finalisé) est reprise plutôt que d'en créer une nouvelle à chaque
        // essai — comme le font déjà ProposalAccept/CustomOfferAccept (audit externe : sans ça,
        // chaque nouvel essai laissait une ligne "jamais payée" orpheline en base). $existingOrder
        // (cherchée plus haut, avant de décider du sort des fichiers déjà envoyés) est la même
        // commande.
        $order = $existingOrder;

        if ($order) {
            $order->update($orderData);
        } else {
            $order = Order::create($orderData);
        }

        if ($promoError = $order->applyPromoCode($this->promoCode)) {
            $this->addError('promoCode', $promoError);

            return;
        }

        try {
            $url = $payments->initiateForOrder($order, $this->paymentMethod, $this->useReferralCredit);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('orders.show', $order)
                ->with('error', 'La commande a été créée mais le paiement n\'a pas pu être initié. Réessayez depuis la page de la commande.');
        }

        return redirect()->away($url);
    }

    public function render()
    {
        return view('livewire.order-create')
            ->layout('components.layouts.app');
    }
}
