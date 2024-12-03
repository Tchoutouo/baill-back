<?php

namespace App\Models;
use App\Models\Profil;
use App\Models\AccessRight;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    //définitions des rélations

    //** Un users ne peut avoir qu'un seul profil */
    public function profils():BelongsTo
    {
        return $this->belongsTo(Profil::class,'profil_id');
    }

    //** Un users ne peut publier 0 ou +sieurs annonces */
    public function annonces():HasMany
    {
        return $this->hasMany(Annonce::class);
    }


    //** Un users ne peut publier 0 ou +sieurs paiements */
    public function paiements():HasMany
    {
        return $this->hasMany(Paiement::class,'paiement_id');
    }

    //** Un user peut avoir un ou plusieurs droit d'accès */
    public function access_rights():BelongsToMany
    {
        return $this->belongsToMany(AccessRight::class,'user_access_rights','access_right_id', 'user_id');
    }

}
