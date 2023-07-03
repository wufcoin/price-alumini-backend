<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGradLevel extends Model
{
    protected $fillable = [
        'user_id', 'grad_level_id'
    ];
}
