<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'opened_by',
        'reason',
        'description',
        'evidences',
        'status',
        'admin_note',
        'resolution',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'evidences' => 'array',
        'resolved_at' => 'datetime',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Résoudre le litige
    public function resolve(string $resolution, int $adminId, string $action = 'refund')
    {
        $this->resolution = $resolution;
        $this->status = 'resolved';
        $this->resolved_at = now();
        $this->resolved_by = $adminId;
        $this->save();

        // Appliquer l'action
        if ($action === 'refund') {
            $this->order->refund();
        } elseif ($action === 'release') {
            $this->order->releasePayment();
        }
    }

    // Scope
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
