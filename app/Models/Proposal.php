<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'user_id',
        'message',
        'proposed_price',
        'delivery_time',
        'status',
    ];

    protected $casts = [
        'proposed_price' => 'decimal:2',
        'delivery_time' => 'integer',
    ];

    // Relations
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    // withTrashed() (audit externe — 8e audit, même principe que Order::prestataire()) : un
    // prestataire peut être soft-deleted alors qu'une proposition encore 'pending' lui est
    // rattachée — sans ceci, la relation renvoyait null et $proposal->prestataire->commissionRate()
    // (dans ProposalAccept::confirm()) plantait avec une erreur 500 dès que le client tentait
    // d'accepter.
    public function prestataire()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    // Accepter la proposition
    public function accept()
    {
        $this->status = 'accepted';
        $this->save();

        // Rejeter les autres propositions
        $this->serviceRequest->proposals()
            ->where('id', '!=', $this->id)
            ->update(['status' => 'rejected']);

        // Fermer la demande
        $this->serviceRequest->update(['status' => 'closed']);
    }
}
