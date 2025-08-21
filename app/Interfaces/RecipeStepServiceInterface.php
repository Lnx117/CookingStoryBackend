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

    /**
     * Добавить один шаг
     */
    public function addStep(Recipe $recipe, array $stepData): RecipeStep;

    /**
     * Обновить один шаг
     */
    public function updateStep(RecipeStep $step, array $stepData): RecipeStep;

    /**
     * Удалить один шаг
     */
    public function removeStep(RecipeStep $step): bool;

    /**
     * Получить шаги рецепта
     */
    public function getStepsForRecipe(Recipe $recipe): array;
}
