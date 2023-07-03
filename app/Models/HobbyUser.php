<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HobbyUser extends Model
{
    use HasFactory;

    protected $table = "hobby_user";

    protected $fillable = ["user_id", "hobby_id"];

    public $timestamps = false;
}
