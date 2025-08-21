<?php

namespace App\Services;

use App\Interfaces\RecipeIngredientServiceInterface;
use App\Models\Recipe;
use App\Interfaces\RecipeIngredientRepositoryInterface;

class RecipeIngredientService implements RecipeIngredientServiceInterface
{
    public function __construct(
        private RecipeIngredientRepositoryInterface $recipeIngredientRepository
    ) {}

    public function attachToRecipe(Recipe $recipe, array $ingredients): void
    {
        $this->recipeIngredientRepository->attach($recipe, $ingredients);
    }

    public function syncWithRecipe(Recipe $recipe, array $ingredients): void
    {
        $this->recipeIngredientRepository->sync($recipe, $ingredients);
    }

    public function detachFromRecipe(Recipe $recipe): void
    {
        $this->recipeIngredientRepository->detach($recipe);
    }
}
