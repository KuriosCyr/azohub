<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'prestataire_id',
        'service_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // withTrashed() : un participant peut être supprimé (anonymizeAndDelete()) alors que la
    // conversation reste consultable par l'autre — sans ceci, la relation renvoyait null et
    // $conversation->otherParticipant()->name (vue conversation-show) plantait à l'affichage.
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id')->withTrashed();
    }

    public function prestataire()
    {
        return $this->belongsTo(User::class, 'prestataire_id')->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function customOffers()
    {
        return $this->hasMany(CustomOffer::class);
    }

    public function hasParticipant(int $userId): bool
    {
        return $this->client_id === $userId || $this->prestataire_id === $userId;
    }

    public function otherParticipant(int $userId): User
    {
        return $userId === $this->client_id ? $this->prestataire : $this->client;
    }
}
