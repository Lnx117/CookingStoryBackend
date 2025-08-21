<?php

namespace App\Interfaces;

use App\Models\Recipe;
use App\Models\RecipeStep;

interface RecipeStepRepositoryInterface
{
    public function create(Recipe $recipe, array $data): RecipeStep;

    public function update(RecipeStep $step, array $data): RecipeStep;

    public function delete(RecipeStep $step): bool;

    public function getByRecipe(Recipe $recipe): \Illuminate\Support\Collection;
}
