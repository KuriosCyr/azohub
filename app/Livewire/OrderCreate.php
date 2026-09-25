<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Service;
use App\Services\PaymentService;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderCreate extends Component
{
    use WithFileUploads;

    public Service $service;
    public string $package = 'basic';
    public string $requirements = '';
    public string $paymentMethod = 'mtn_momo';
    public $attachments = [];

    public function mount()
    {
        $serviceId = request()->query('service');

        abort_if(!$serviceId, 404);

        $this->service = Service::with(['prestataire', 'category'])->findOrFail($serviceId);
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

        $commission = round($this->service->price * $this->service->prestataire->commissionRate(), 2);
        $clientFee = round($this->service->price * Order::CLIENT_FEE_RATE, 2);

        $order = Order::create([
            'client_id'        => auth()->id(),
            'prestataire_id'   => $this->service->user_id,
            'service_id'       => $this->service->id,
            'requirements'     => $this->requirements,
            'attachments'      => !empty($uploadedAttachments) ? $uploadedAttachments : null,
            'amount'           => $this->service->price,
            'commission'       => $commission,
            'client_fee'       => $clientFee,
            'prestataire_amount' => $this->service->price - $commission,
            'delivery_time'    => $this->service->delivery_time,
            'status'           => 'pending_payment',
            'payment_status'   => 'pending',
        ]);

        try {
            $url = $payments->initiateForOrder($order, $this->paymentMethod);
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
