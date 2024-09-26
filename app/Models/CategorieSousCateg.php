<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategorieSousCateg extends Model
{
    use HasFactory;
    protected $table = 'categorie_sous_categories';

    protected $guarded = [
        'id'
    ];
}
