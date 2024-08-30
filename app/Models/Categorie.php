<?php

namespace App\Models;
use App\Models\Annonce;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Categorie extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $guarded = [
        'id'
    ];


    /** Une categories peut être attribuer à une ou plusieurs annonces */
    public function annonces():BelongsToMany
    {
        return $this->belongsToMany(Annonce::class,'categorie_annonces','categorie_id', 'annonce_id');
    }

}
