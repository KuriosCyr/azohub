<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'client_id',
        'prestataire_id',
        'service_id',
        'title',
        'description',
        'price',
        'delivery_days',
        'revisions_included',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'delivery_days' => 'integer',
        'revisions_included' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->status === 'pending' && $this->expires_at && $this->expires_at->isPast();
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

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

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function decline()
    {
        $this->update(['status' => 'declined']);
    }

    public function expire()
    {
        $this->update(['status' => 'expired']);
    }
}
