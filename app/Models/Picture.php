<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Annonce;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Picture extends Model
{
    use HasFactory;

    protected $table = 'pictures';

    protected $guarded = [
        'id'
    ];

    /** Une image appartient à une seule annonce */
    public function annonces(): BelongsTo
    {
        return $this->belongsTo(Annonce::class,'annonce_id');
    }
}
