<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolUser extends Model
{
    use HasFactory;

    protected $table = "school_user";

    protected $fillable = ["user_id", "school_id"];

    public $timestamps = false;
}
