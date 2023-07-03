<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name', 'zip_code'
    ];

    public function delete()
    {
        $this->hasMany(UserCity::class, 'city_id', 'id')->delete();

        return parent::delete();
    }
}
