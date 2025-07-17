<?php

namespace App\Models;
use App\Models\Annonce;
use App\Models\SousCategorie;
use App\Models\TranslateCategorie;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /** Une categorie peut avoir une ou plusieurs sous_categories */
    public function sousCategorie():BelongsToMany
    {
        return $this->belongsToMany(SousCategorie::class,'categorie_sous_categories','categorie_id', 'sous_categorie_id');
    }

    /** La version anglaise de chaque categories */
    public function translate():HasMany
    {
        return $this->hasMany(TranslateCategorie::class,'categorie_id');
    }
}
