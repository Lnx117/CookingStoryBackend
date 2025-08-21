<?php

namespace App\Interfaces;

use App\Models\Recipe;

interface RecipeIngredientServiceInterface
{
    /**
     * Привязать ингредиенты к рецепту
     */
    public function attachToRecipe(Recipe $recipe, array $ingredients): void;

    /**
     * Синхронизировать ингредиенты рецепта
     */
    public function syncWithRecipe(Recipe $recipe, array $ingredients): void;

    /**
     * Удалить все связи
     */
    public function detachFromRecipe(Recipe $recipe): void;
}
