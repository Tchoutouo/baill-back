<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategorieAnnonce extends Model
{
    use HasFactory;
    protected $table = 'categorie_annonces';

    protected $guarded = [
        'id'
    ];
}
