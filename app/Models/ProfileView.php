<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'prestataire_id',
        'viewer_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function prestataire()
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    public function viewer()
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }
}
