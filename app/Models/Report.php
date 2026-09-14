<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'reporter_id',
        'reason',
        'details',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // Relations
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReviewing($query)
    {
        return $query->where('status', 'reviewing');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeDismissed($query)
    {
        return $query->where('status', 'dismissed');
    }

    // Helpers
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isReviewing()
    {
        return $this->status === 'reviewing';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isDismissed()
    {
        return $this->status === 'dismissed';
    }

    // Libellés des raisons
    public static function reasonLabels()
    {
        return [
            'inappropriate_content' => 'Contenu inapproprié',
            'scam' => 'Arnaque / Fraude',
            'copyright_violation' => 'Violation de droits d\'auteur',
            'misleading_info' => 'Informations trompeuses',
            'poor_quality' => 'Mauvaise qualité',
            'spam' => 'Spam / Publicité abusive',
            'other' => 'Autre',
        ];
    }

    public function getReasonLabelAttribute()
    {
        return self::reasonLabels()[$this->reason] ?? $this->reason;
    }

    // Libellés des statuts
    public static function statusLabels()
    {
        return [
            'pending' => 'En attente',
            'reviewing' => 'En cours d\'examen',
            'resolved' => 'Résolu',
            'dismissed' => 'Rejeté',
        ];
    }

    public function getStatusLabelAttribute()
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}