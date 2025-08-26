<?php

namespace App\Services;

use App\Interfaces\FileStoreServiceInterface;
use App\Interfaces\RecipeStepServiceInterface;
use App\Models\Recipe;
use App\Interfaces\RecipeStepRepositoryInterface;
use App\Models\RecipeStep;

class RecipeStepService implements RecipeStepServiceInterface
{
    public function __construct(
        private RecipeStepRepositoryInterface $stepRepository,
        private FileStoreServiceInterface $fileStoreService
    ) {}

    public function createForRecipe(Recipe $recipe, array $steps): void
    {
        foreach ($steps as $stepData) {
            //сохраняем изображение
            $path = $this->fileStoreService->storeFromRequest(
                $stepData['image'],
                'recipes/steps/images',
            );
            $stepData['image'] = $path[0] ?? null;
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
}
