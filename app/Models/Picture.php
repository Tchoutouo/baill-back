<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Annonce;

class Picture extends Model
{
    use HasFactory;

    protected $table = 'pictures';

    protected $guarded = [
        'id'
    ];

    /** Une image appartient à une seule annonce */
    public function annonces(): HasMany
    {
        return $this->hasMany(Annonce::class);
    }
}
