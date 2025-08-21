<?php

namespace App\Repositories;

use App\Models\Recipe;
use App\Models\Tag;
use App\Interfaces\RecipeTagRepositoryInterface;

class RecipeTagRepository implements RecipeTagRepositoryInterface
{
    public function attach(Recipe $recipe, array $tags): void
    {
        $recipe->tags()->attach($tags);
    }

    public function sync(Recipe $recipe, array $tags): void
    {
        $recipe->tags()->sync($tags);
    }

    public function detach(Recipe $recipe): void
    {
        $recipe->tags()->detach();
    }
}
