<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDegree extends Model
{
    protected $fillable = [
        'user_id', 'degree_id'
    ];
}
