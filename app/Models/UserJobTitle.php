<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserJobTitle extends Model
{
    protected $fillable = [
        'user_id', 'job_title_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function job_title()
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id', 'id');
    }
}
