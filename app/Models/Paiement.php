<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasFactory;

    protected $table = 'paiement';

    protected $guarded = [
        'id'
    ];
    /** Un paiement peut être effectuer par un user */
    public function users():BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /** Un paiement est assigné par un abonnement */
    public function abonnements():BelongsTo
    {
        return $this->belongsTo(Abonnement::class,'abonnement_id');
    }
}
