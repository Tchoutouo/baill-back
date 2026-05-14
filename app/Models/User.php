<?php

namespace App\Models;
use App\Models\Profil;
use App\Models\AccessRight;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPassword;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'picture',
        'number',
        'whatsapp_number',
        'cni',
        'site_url',
        'neighborhood',
        'city',
        'country',
        'date_of_birth',
        'place_of_birth',
        'sex',
        // profil_id, status, qte_free, email_verified_at intentionally excluded
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

    public function sendPasswordResetNotification($token)
    {

        try {
            $status = $this->notify(new CustomResetPassword($token));
            return $status;
        } catch (\Exception $e) {
            Log::error('Error lors de l\'envoi email lien de reinitialisation : ' . $e->getMessage());
            return response()->json([
                    'success' => false,
                    'message' => "Echec envoie d'email...",
                ]
            );
        }
    }
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
        return $this->hasMany(Paiement::class, 'user_id');
    }

    //** Un user peut avoir un ou plusieurs droit d'accès */
    public function access_rights():BelongsToMany
    {
        return $this->belongsToMany(AccessRight::class, 'user_access_rights', 'user_id', 'access_right_id');
    }

}