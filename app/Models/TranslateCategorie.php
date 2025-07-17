<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categorie;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TranslateCategorie extends Model
{
    use HasFactory;

    protected $table = 'translate_categories';

    protected $guarded = [
        'id'
    ];

        /** La version anglaise de chaque categories */
    public function translate():BelongsTo
    {
        return $this->belongsTo(Categorie::class,'categorie_id');
    }

}
