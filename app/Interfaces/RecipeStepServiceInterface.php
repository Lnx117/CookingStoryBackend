<?php

namespace App\Interfaces;

use App\Models\Recipe;
use App\Models\RecipeStep;

interface RecipeStepServiceInterface
{
    /**
     * Создать шаги для рецепта (bulk insert)
     */
    public function createForRecipe(Recipe $recipe, array $steps): void;

    /**
     * Синхронизировать шаги (удалить старые, вставить/обновить новые)
     */
    public function syncForRecipe(Recipe $recipe, array $steps): void;

    /**
     * Удалить все шаги рецепта
     */
    public function deleteForRecipe(Recipe $recipe): void;
}
