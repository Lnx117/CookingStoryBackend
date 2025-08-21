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

    public function attachToRecipe(Recipe $recipe, array $tags): void
    {
        $this->tagRepository->attach($recipe, $tags);
    }

    public function syncWithRecipe(Recipe $recipe, array $tags): void
    {
        $this->tagRepository->sync($recipe, $tags);
    }

    public function detachFromRecipe(Recipe $recipe): void
    {
        $this->tagRepository->detach($recipe);
    }
}
