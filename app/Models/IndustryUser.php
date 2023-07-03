<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndustryUser extends Model
{
    use HasFactory;

    protected $table = 'industry_user';

    protected $fillable = [
        'user_id', 'industry_id'
    ];

    public $timestamps = false;
}
