<?php

namespace App\Repositories;

use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Interfaces\RecipeStepRepositoryInterface;

class RecipeStepRepository implements RecipeStepRepositoryInterface
{
    public function create(Recipe $recipe, array $data): RecipeStep
    {
        return $recipe->steps()->create($data);
    }

    public function update(RecipeStep $step, array $data): RecipeStep
    {
        $step->update($data);
        return $step;
    }

    public function delete(RecipeStep $step): bool
    {
        return $step->delete();
    }

    public function getByRecipe(Recipe $recipe): \Illuminate\Support\Collection
    {
        return $recipe->steps()->orderBy('position')->get();
    }
}
