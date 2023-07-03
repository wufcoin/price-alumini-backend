<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssociationUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'association_id'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function association() {
        return $this->belongsTo(Association::class, 'association_id', 'id');
    }
}
