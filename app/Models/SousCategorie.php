<?php

namespace App\Models;
use App\Models\Categorie;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SousCategorie extends Model
{
    use HasFactory;
    protected $table = 'sous_categories';

    protected $guarded = [
        'id'
    ];

    /** Une sous_categories peut être attribuer à une ou plusieurs categories */
    public function categorie():BelongsToMany
    {
        return $this->belongsToMany(Categorie::class,'categorie_sous_categories', 'sous_categorie_id', 'categorie_id');
    }
}
