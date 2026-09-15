<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'category_id',
        'title',
        'description',
        'budget',
        'deadline',
        'city',
        'address',
        'attachments',
        'status',
        'proposals_count',
        'expires_at',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'deadline' => 'integer',
        'attachments' => 'array',
        'proposals_count' => 'integer',
        'expires_at' => 'datetime',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function acceptedProposal()
    {
        return $this->hasOne(Proposal::class)->where('status', 'accepted');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
