<?php
namespace App\Interfaces;

use App\Http\Requests\BannerUpdateRequest;
use App\Models\Banner;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface RecipeRepositoryInterface
{
    /** Создать рецепт */
    public function create(array $data): Recipe;

    /** Обновить рецепт */
    public function update(Recipe $recipe, array $data): Recipe;

    /** Удалить рецепт */
    public function delete(Recipe $recipe): bool;
//
//    /** Найти по ID (с опциональной подгрузкой связей) */
//    public function findById(int $id, array $with = []): ?Recipe;
//
//    /** Найти по slug (с опциональной подгрузкой связей) */
//    public function findBySlug(string $slug, array $with = []): ?Recipe;
//
    /** Пагинация с простыми фильтрами (для админки/каталога) */
    public function paginate(array $filters = [], int $perPage = 15, array $with = []): LengthAwarePaginator;
//
//    /** Пагинация рецептов автора */
//    public function forAuthor(int $userId, array $filters = [], int $perPage = 15, array $with = []): LengthAwarePaginator;
//
//    /** Проверка уникальности slug (с игнорированием текущего ID при апдейте) */
//    public function existsSlug(string $slug, ?int $ignoreId = null): bool;
}
