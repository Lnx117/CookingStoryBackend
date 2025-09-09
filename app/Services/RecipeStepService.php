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
            $imagePath = null;

            // Проверяем, есть ли изображение
            if (!empty($stepData['image'])) {
                $path = $this->fileStoreService->storeFromRequest(
                    $stepData['image'],
                    'recipes/steps/images',
                );
                $imagePath = $path[0] ?? null;
            }

            // Создаем шаг с изображением или без
            $this->stepRepository->create($recipe, [
                'step_number' => $stepData['step_number'],
                'description' => $stepData['description'],
                'image' => $imagePath,
            ]);
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
