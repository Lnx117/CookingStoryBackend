<?php

namespace App\Services;

use App\Interfaces\RecipeTagServiceInterface;
use App\Models\Recipe;
use App\Interfaces\RecipeTagRepositoryInterface;

class RecipeTagService implements RecipeTagServiceInterface
{
    public function __construct(
        private RecipeTagRepositoryInterface $tagRepository
    ) {}

    public function attachToRecipe(Recipe $recipe, array $tagIds): void
    {
        $this->tagRepository->attach($recipe, $tagIds);
    }

    public function syncWithRecipe(Recipe $recipe, array $tagIds): void
    {
        $this->tagRepository->sync($recipe, $tagIds);
    }

    public function detachFromRecipe(Recipe $recipe): void
    {
        $this->tagRepository->detach($recipe);
    }
}
