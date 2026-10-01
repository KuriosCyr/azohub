<?php

namespace App\Livewire;

use App\Livewire\Concerns\RateLimitsActions;
use App\Models\Order;
use App\Models\Message;
use App\Notifications\NewMessageReceived;
use App\Support\ContactInfoDetector;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderChat extends Component
{
    use WithFileUploads, RateLimitsActions;

    public Order $order;
    public $message = '';
    public $attachments = [];

    // Même principe que ConversationShow::$messagesLimit : évite de recharger tout l'historique
    // à chaque sondage (wire:poll.5s) sur une commande avec beaucoup d'échanges.
    // #[Locked] (audit externe — 5e audit) : une propriété Livewire publique sans ça est
    // modifiable depuis le navigateur (snapshot renvoyé au client puis réhydraté tel quel) — un
    // client pouvait la forcer à une valeur énorme pour charger tout l'historique à chaque
    // sondage au lieu de seulement $messagesLimit messages.
    #[Locked]
    public int $messagesLimit = 50;

    // Plafond (audit externe — 6e audit) : #[Locked] empêche de fixer $messagesLimit directement
    // depuis le navigateur, mais loadMoreMessages() restait appelable en boucle sans aucune limite
    // — un client scripté pouvait l'appeler de nombreuses fois pour revenir à charger tout
    // l'historique à chaque sondage, exactement ce que #[Locked] visait à empêcher.
    private const MAX_MESSAGES_LIMIT = 1000;

    public function loadMoreMessages(): void
    {
        $this->messagesLimit = min($this->messagesLimit + 50, self::MAX_MESSAGES_LIMIT);
    }

    protected $rules = [
        'message' => 'required|string|max:2000',
        'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,rar,mp4,mp3,mov',
    ];

    public function getContainsContactInfoProperty(): bool
    {
        return ContactInfoDetector::detect($this->message);
    }

    protected $messages = [
        'message.required' => 'Veuillez saisir un message.',
        'message.max' => 'Le message ne peut pas dépasser 2000 caractères.',
        'attachments.*.max' => 'Chaque fichier ne peut pas dépasser 10 MB.',
        'attachments.*.mimes' => 'Type de fichier non autorisé.',
    ];

    public function mount(Order $order)
    {
        abort_unless(
            $order->client_id === Auth::id() || $order->prestataire_id === Auth::id(),
            403
        );

        $this->order = $order;
        $this->markMessagesAsRead();
    }

    public function markMessagesAsRead()
    {
        Message::where('order_id', $this->order->id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function sendMessage()
    {
        if ($this->tooManyActions('order-chat-message', maxAttempts: 20, field: 'message')) {
            return;
        }

        $this->validate();

        // Déterminer le destinataire
        $receiverId = $this->order->client_id === Auth::id() 
            ? $this->order->prestataire_id 
            : $this->order->client_id;

        // Upload des pièces jointes
        $uploadedAttachments = [];
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $path = $file->store('messages/' . $this->order->id, 'local');
                $uploadedAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

        // Créer le message
        $newMessage = Message::create([
            'order_id' => $this->order->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'message' => $this->message,
            'attachments' => !empty($uploadedAttachments) ? $uploadedAttachments : null,
        ]);

        $newMessage->receiver->notify(new NewMessageReceived($newMessage));

        // Reset explicite des champs
        $this->message = '';
        $this->attachments = [];
        $this->reset(['message', 'attachments']);

        // Marquer les messages comme lus
        $this->markMessagesAsRead();

        // Dispatch event pour scroll
        $this->dispatch('message-sent');
    }

    public function refreshMessages()
    {
        // Méthode vide pour le polling - le render() se chargera de recharger
        $this->markMessagesAsRead();
    }

    public function render()
    {
        $totalMessages = Message::where('order_id', $this->order->id)->count();

        $messages = Message::where('order_id', $this->order->id)
            ->with(['sender', 'receiver'])
            ->latest()
            ->limit($this->messagesLimit)
            ->get()
            ->sortBy('id')
            ->values();

        return view('livewire.order-chat', [
            'messages' => $messages,
            'hasMoreMessages' => $totalMessages > $this->messagesLimit,
        ]);
    }
}