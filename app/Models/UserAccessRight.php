<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserAccessRight extends Model
{
    use HasFactory;
    protected $table = 'user_access_rights';

    protected $guarded = [
        'id'
    ];
}
