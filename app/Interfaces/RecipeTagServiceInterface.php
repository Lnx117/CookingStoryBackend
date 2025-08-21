<?php

namespace App\Interfaces;

use App\Models\Recipe;

interface RecipeTagServiceInterface
{
    public function attachToRecipe(Recipe $recipe, array $tagIds): void;

    public function syncWithRecipe(Recipe $recipe, array $tagIds): void;

    public function detachFromRecipe(Recipe $recipe): void;
}
