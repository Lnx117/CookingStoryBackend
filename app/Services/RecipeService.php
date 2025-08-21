<?php

namespace App\Services;

use App\Interfaces\RecipeIngredientServiceInterface;
use App\Interfaces\RecipeRepositoryInterface;
use App\Interfaces\RecipeServiceInterface;
use App\Interfaces\RecipeStepServiceInterface;
use App\Interfaces\RecipeTagServiceInterface;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecipeService implements RecipeServiceInterface
{

    public function __construct(
        private RecipeRepositoryInterface $recipeRepository,
        private RecipeIngredientServiceInterface $recipeIngredientService,
        private RecipeStepServiceInterface $recipeStepService,
        private RecipeTagServiceInterface $recipeTagService
    ) {}


    public function createRecipe(array $data, User $user): Recipe
    {
        return DB::transaction(function () use ($data, $user) {
            // 1. Создаём сам рецепт
            $recipe = $this->recipeRepository->create([
                'user_id'      => $user->id,
                'title'        => $data['title'],
                'slug'         => Str::slug($data['title']),
                'description'  => $data['description'] ?? null,
                'preview_image'=> $data['preview_image'] ?? null,
                'servings'     => $data['servings'],
                'cooking_time' => $data['cooking_time'] ?? null,
                'is_published' => $data['is_published'] ?? false,
            ]);

            // 2. Привязываем ингредиенты, шаги, теги
            $this->recipeIngredientService->attachToRecipe($recipe, $data['ingredients'] ?? []);
            $this->recipeStepService->createForRecipe($recipe, $data['steps'] ?? []);
            $this->recipeTagService->attachToRecipe($recipe, $data['tags'] ?? []);

            // 3. Возвращаем рецепт с загруженными связями
            return $recipe->load(['ingredients', 'steps', 'tags']);
        });
    }

    /**
     * Обновить рецепт
     */
    public function updateRecipe(Recipe $recipe, array $data): Recipe
    {
        return DB::transaction(function () use ($recipe, $data) {
            $result = $this->recipeRepository->update($recipe, [
                'title'        => $data['title'],
                'slug'         => Str::slug($data['title']),
                'description'  => $data['description'] ?? null,
                'preview_image'=> $data['preview_image'] ?? null,
                'servings'     => $data['servings'],
                'cooking_time' => $data['cooking_time'] ?? null,
                'is_published' => $data['is_published'] ?? false,
            ]);

            $this->recipeIngredientService->syncWithRecipe($result, $data['ingredients'] ?? []);
            $this->recipeStepService->syncForRecipe($result, $data['steps'] ?? []);
            $this->recipeTagService->syncWithRecipe($result, $data['tags'] ?? []);

            return $result->load(['ingredients', 'steps', 'tags']);
        });
    }

    /**
     * Удалить рецепт
     */
    public function deleteRecipe(Recipe $recipe): bool
    {
        return DB::transaction(function () use ($recipe) {
            $this->recipeIngredientService->detachFromRecipe($recipe);
            $this->recipeStepService->deleteForRecipe($recipe);
            $this->recipeTagService->detachFromRecipe($recipe);

            return $this->recipeRepository->delete($recipe);
        });
    }

    /**
     * Получить список рецептов с пагинацией
     */
        public function listRecipes(array $filters = [], int $perPage = 15, array $with = ['user']): \Illuminate\Contracts\Pagination\LengthAwarePaginator
        {
            return $this->recipeRepository->paginate($filters, $perPage, $with);
        }

    /**
     * Получить рецепт по ID (с подгрузкой связей)
     */
    public function getRecipeById(int $id, array $with = ['ingredients', 'steps', 'tags', 'user']): ?Recipe
    {
        return $this->recipeRepository->findById($id, $with);
    }
//
//    /**
//     * Получить рецепт по slug
//     */
//    public function getRecipeBySlug(string $slug, array $with = ['ingredients', 'steps', 'tags', 'author']): ?Recipe
//    {
//        return $this->recipes->findBySlug($slug, $with);
//    }
//
//
    /**
     * Получить список рецептов конкретного пользователя
     */
    public function listUserRecipes(int $userId, array $filters = [], int $perPage = 15, array $with = ['user']): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $filters['author_id'] = $userId;

        return $this->recipeRepository->paginate($filters, $perPage, ['user']);
    }

}
