<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'name', 'high_school', 'color_1', 'color_2', 'logo_1', 'logo_2', 'slogan', 'acronym', 'banner'
    ];
}
