<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'preview_image',
        'servings',
        'cooking_time',
        'calories_total',
        'is_published',
        'proteins_total',
        'fats_total',
        'carbs_total',
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'servings' => 'integer',
        'cooking_time' => 'integer',
        'calories_total' => 'integer',
        'proteins_total' => 'decimal:2',
        'fats_total' => 'decimal:2',
        'carbs_total' => 'decimal:2',
    ];
    // Автор рецепта
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Ингредиенты
    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->withPivot('weight_grams')
            ->withTimestamps();
    }

    // Шаги приготовления
    public function steps()
    {
        return $this->hasMany(RecipeStep::class);
    }

    // Теги
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'recipe_tag');
    }

    // Лайки
    public function likes()
    {
        return $this->hasMany(RecipeLike::class);
    }

    // Комментарии
    public function comments()
    {
        return $this->hasMany(RecipeComment::class);
    }
}
