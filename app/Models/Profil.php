<?php

namespace App\Models;
use App\Models\User;
use App\Models\AccessRight;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Profil extends Model
{
    use HasFactory;

    protected $table = 'profils';

    protected $guarded = [
        'id'
    ];

    /** Un profil peut être attribuer à un ou plusieurs users */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }


    /** Un profil peut avoir un ou plusieurs droit d'access */
    public function access_rights(): BelongsToMany
    {
        return $this->belongsToMany(AccessRight::class,'profil_access_right','profils_id','access_rights_id');
    }
}
