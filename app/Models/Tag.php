<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'recipe_tag');
    }
}
