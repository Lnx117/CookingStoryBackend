<?php

namespace App\Services;

use App\Interfaces\RecipeStepServiceInterface;
use App\Models\Recipe;
use App\Interfaces\RecipeStepRepositoryInterface;
use App\Models\RecipeStep;

class RecipeStepService implements RecipeStepServiceInterface
{
    public function __construct(
        private RecipeStepRepositoryInterface $stepRepository
    ) {}

    public function createForRecipe(Recipe $recipe, array $steps): void
    {
        foreach ($steps as $stepData) {
            $this->stepRepository->create($recipe, $stepData);
        }
    }

    public function syncForRecipe(Recipe $recipe, array $steps): void
    {
        // удалить старые
        $this->deleteForRecipe($recipe);
        // вставить новые
        $this->createForRecipe($recipe, $steps);
    }

    public function deleteForRecipe(Recipe $recipe): void
    {
        foreach ($recipe->steps as $step) {
            $this->stepRepository->delete($step);
        }
    }

    public function addStep(Recipe $recipe, array $data): RecipeStep
    {
        return $this->stepRepository->create($recipe, $data);
    }

    public function updateStep(RecipeStep $step, array $data): RecipeStep
    {
        return $this->stepRepository->update($step, $data);
    }

    public function removeStep(RecipeStep $step): bool
    {
        return $this->stepRepository->delete($step);
    }

    public function getStepsForRecipe(Recipe $recipe): array
    {
        return $this->stepRepository->getByRecipe($recipe);
    }
}
