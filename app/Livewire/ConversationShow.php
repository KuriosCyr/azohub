<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\CustomOffer;
use App\Notifications\CustomOfferDeclined;
use App\Notifications\CustomOfferReceived;
use App\Notifications\NewConversationMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ConversationShow extends Component
{
    use WithFileUploads;

    public Conversation $conversation;

    public string $message = '';
    public $attachments = [];

    public bool $showOfferForm = false;
    public string $offerTitle = '';
    public string $offerDescription = '';
    public $offerPrice = '';
    public $offerDeliveryDays = '';

    public function mount(Conversation $conversation)
    {
        abort_unless($conversation->hasParticipant(Auth::id()), 403);

        $this->conversation = $conversation;

        $this->conversation->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function getIsPrestataireProperty(): bool
    {
        return Auth::id() === $this->conversation->prestataire_id;
    }

    public function sendMessage()
    {
        $this->validate([
            'message' => 'nullable|string|max:2000',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        if (blank($this->message) && empty($this->attachments)) {
            $this->addError('message', 'Écrivez un message ou joignez au moins un fichier.');
            return;
        }

        $uploadedAttachments = [];
        foreach ($this->attachments as $file) {
            $path = $file->store('messages/conversations/' . $this->conversation->id, 'public');
            $uploadedAttachments[] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
                'uploaded_at' => now()->toDateTimeString(),
            ];
        }

        $msg = $this->conversation->messages()->create([
            'sender_id' => Auth::id(),
            'message' => $this->message ?: null,
            'attachments' => !empty($uploadedAttachments) ? $uploadedAttachments : null,
        ]);

        $this->conversation->update(['last_message_at' => now()]);

        $receiver = $this->conversation->otherParticipant(Auth::id());
        $receiver->notify(new NewConversationMessage($msg));

        $this->reset(['message', 'attachments']);
        $this->conversation->refresh();
    }

    public function toggleOfferForm()
    {
        $this->showOfferForm = !$this->showOfferForm;
    }

    public function sendOffer()
    {
        abort_unless($this->isPrestataire, 403);

        $validated = $this->validate([
            'offerTitle' => 'required|string|max:150',
            'offerDescription' => 'required|string|max:2000',
            'offerPrice' => 'required|numeric|min:100|max:100000000',
            'offerDeliveryDays' => 'required|integer|min:1|max:365',
        ], [
            'offerTitle.required' => 'Veuillez indiquer un titre pour cette offre.',
            'offerDescription.required' => 'Veuillez décrire ce que couvre cette offre.',
            'offerPrice.required' => 'Veuillez indiquer un prix.',
            'offerDeliveryDays.required' => 'Veuillez indiquer un délai de livraison.',
        ]);

        $offer = CustomOffer::create([
            'conversation_id' => $this->conversation->id,
            'client_id' => $this->conversation->client_id,
            'prestataire_id' => $this->conversation->prestataire_id,
            'service_id' => $this->conversation->service_id,
            'title' => $validated['offerTitle'],
            'description' => $validated['offerDescription'],
            'price' => $validated['offerPrice'],
            'delivery_days' => $validated['offerDeliveryDays'],
            'status' => 'pending',
        ]);

        $msg = $this->conversation->messages()->create([
            'sender_id' => Auth::id(),
            'custom_offer_id' => $offer->id,
        ]);

        $this->conversation->update(['last_message_at' => now()]);

        $this->conversation->client->notify(new CustomOfferReceived($offer));

        $this->reset(['offerTitle', 'offerDescription', 'offerPrice', 'offerDeliveryDays', 'showOfferForm']);
        $this->conversation->refresh();
    }

    public function declineOffer($offerId)
    {
        $offer = $this->conversation->customOffers()
            ->where('id', $offerId)
            ->where('status', 'pending')
            ->firstOrFail();

        abort_unless(Auth::id() === $this->conversation->client_id, 403);

        $offer->decline();
        $offer->prestataire->notify(new CustomOfferDeclined($offer));

        $this->conversation->refresh();

        session()->flash('success', 'Offre déclinée.');
    }

    public function render()
    {
        $this->conversation->load([
            'client', 'prestataire',
            'messages' => function ($query) {
                $query->with(['sender', 'customOffer'])->oldest();
            },
        ]);

        return view('livewire.conversation-show')
            ->layout('components.layouts.app');
    }
}
