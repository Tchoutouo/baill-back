<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Abonnement extends Model
{
    use HasFactory;

    protected $table = 'abonnements';

    protected $guarded = [
        'id'
    ];

    /** Un abonnement peut être attribuer à un ou plusieurs annonces */
    public function annonces(): HasMany
    {
        return $this->hasMany(Annonce::class);
    }

    /** Un abonnement peut être associer à un ou plusieurs paiements */
    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }
}
