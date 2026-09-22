<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'viewer_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
