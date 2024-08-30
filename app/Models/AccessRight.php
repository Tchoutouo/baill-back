<?php

namespace App\Models;
use App\Models\Profil;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class AccessRight extends Model
{
    use HasFactory;

    protected $table = 'access_rights';

    protected $guarded = [
        'id'
    ];


    /** Un droit d'access peut être attribuer à un ou plusieurs profils */
    public function profils():BelongsToMany
    {
        return $this->belongsToMany(Profil::class,'profil_access_right','access_rights_id', 'profils_id');
    }

    /** Un droit d'access peut être attribuer à un ou plusieurs utilisateurs */
    public function users():BelongsToMany
    {
        return $this->belongsToMany(User::class,'user_access_rights','access_right_id', 'user_id');
    }
}
