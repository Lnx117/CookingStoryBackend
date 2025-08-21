<?php

namespace App\Interfaces;

use App\Models\Ingredient;
use App\Models\Recipe;

interface RecipeIngredientRepositoryInterface
{
    public function attach(Recipe $recipe, array $ingredients): void;

    public function sync(Recipe $recipe, array $ingredients): void;

    public function detach(Recipe $recipe): void;
}
