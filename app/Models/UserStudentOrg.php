<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStudentOrg extends Model
{
    protected $fillable = [
        'user_id', 'student_org_id'
    ];
}
