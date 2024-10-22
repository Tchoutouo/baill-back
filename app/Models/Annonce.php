<?php

namespace App\Models;

use App\Models\Categorie;
use App\Models\Picture;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Annonce extends Model
{
    use HasFactory;

    protected $table = 'annonces';

    protected $guarded = [
        'id'
    ];


    /** Une categories peut être attribuer à une ou plusieurs annonces */
    public function categories():BelongsToMany
    {
        return $this->belongsToMany(Categorie::class,'categorie_annonces','annonce_id','categorie_id');
    }

    /** Une annonce peut être publier par un user */
    public function users():BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /** Une annonce peut appartenir un abonnement */
    public function abonnements():BelongsTo
    {
        return $this->belongsTo(Abonnement::class,'abonnement_id');
    }

    /** Une annonce peut avoir image */

    public function pictures():HasMany
    {
        return $this->hasMany(Picture::class,'picture_id');
    }
}
