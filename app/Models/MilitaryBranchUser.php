<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilitaryBranchUser extends Model
{
    use HasFactory;

    protected $table = "military_branch_user";

    protected $fillable = ["user_id", "military_branch_id"];

    public $timestamps = false;
}