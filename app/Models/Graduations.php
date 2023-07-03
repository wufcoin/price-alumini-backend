<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Graduations extends Model {
    protected $table = "graduations";

    protected $fillable = ["user_id", "level"];

}