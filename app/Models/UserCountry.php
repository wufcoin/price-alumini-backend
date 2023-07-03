<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCountry extends Model
{
    protected $fillable = [
        'country_id', 'user_id'
    ];
}
