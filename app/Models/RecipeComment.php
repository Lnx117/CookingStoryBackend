<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeComment extends Model
{
    protected $fillable = [
        'recipe_id',
        'user_id',
        'content',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
