<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilitaryCodeUser extends Model
{
    use HasFactory;

    protected $table = "military_code_user";

    protected $fillable = ["user_id", "military_code_id"];

    public $timestamps = false;
}