<?php

namespace App\Repositories;

use App\Models\Ingredient;
use App\Interfaces\RecipeIngredientRepositoryInterface;
use App\Models\Recipe;

class RecipeIngredientRepository implements RecipeIngredientRepositoryInterface
{
    public function attach(Recipe $recipe, array $ingredients): void
    {
        $syncData = [];
        foreach ($ingredients as $item) {
            $syncData[$item['id']] = ['weight_grams' => $item['weight_grams']];
        }
        $recipe->ingredients()->attach($syncData);
    }

    public function sync(Recipe $recipe, array $ingredients): void
    {
        $syncData = [];
        foreach ($ingredients as $item) {
            $syncData[$item['id']] = ['weight_grams' => $item['weight_grams']];
        }
        $recipe->ingredients()->sync($syncData);
    }

    public function detach(Recipe $recipe): void
    {
        $recipe->ingredients()->detach();
    }
}
