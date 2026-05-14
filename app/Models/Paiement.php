<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasFactory;

    protected $table = 'paiements';

    protected $fillable = [
        'mode_paiement',
        'montant',
        'date_paiement',
        'number',
        'reference',
        'user_id',
        'abonnement_id',
        // statut is system-controlled — excluded
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
