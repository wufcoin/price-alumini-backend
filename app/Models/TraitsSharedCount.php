<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TraitsSharedCount extends Model
{
    use HasFactory;

    protected $table = "traits_shared_count";

    protected $fillable = ["user_id", "user_id_other_user","connection_size", "countries_count", 'graduation_count','association_count', 'schools_count',
        'military_branches_count', 'military_codes_count', 'associations_count', 'hobbies_count', 'industries_count', 'total_traits_shared_count'];

    public $timestamps = false;
}