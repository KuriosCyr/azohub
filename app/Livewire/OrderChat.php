<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Message;
use App\Notifications\NewMessageReceived;
use App\Support\ContactInfoDetector;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderChat extends Component
{
    use WithFileUploads;

    public Order $order;
    public $message = '';
    public $attachments = [];

    protected $rules = [
        'message' => 'required|string|max:2000',
        'attachments.*' => 'nullable|file|max:10240',
    ];

    public function getContainsContactInfoProperty(): bool
    {
        return ContactInfoDetector::detect($this->message);
    }

    protected $messages = [
        'message.required' => 'Veuillez saisir un message.',
        'message.max' => 'Le message ne peut pas dépasser 2000 caractères.',
        'attachments.*.max' => 'Chaque fichier ne peut pas dépasser 10 MB.',
    ];

    public function mount(Order $order)
    {
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
        $this->validate();

        // Déterminer le destinataire
        $receiverId = $this->order->client_id === Auth::id() 
            ? $this->order->prestataire_id 
            : $this->order->client_id;

        // Upload des pièces jointes
        $uploadedAttachments = [];
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $path = $file->store('messages/' . $this->order->id, 'public');
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
        // Charger les messages à chaque render
        $messages = Message::where('order_id', $this->order->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.order-chat', [
            'messages' => $messages
        ]);
    }
}