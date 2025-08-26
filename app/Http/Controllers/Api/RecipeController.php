<?php

namespace App\Http\Controllers\Api;

use App\Enums\LogLevels;
use App\Facades\ClickHouseLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecipeListRequest;
use App\Http\Requests\RecipeRequest;
use App\Http\Responses\ApiResponse;
use App\Interfaces\RecipeServiceInterface;
use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller {

    protected RecipeServiceInterface $recipeService;

    public function __construct(RecipeServiceInterface $recipeService) {
        $this->recipeService = $recipeService;
    }

    /**
     * @OA\Post(
     *     path="/recipes/create-recipe",
     *     summary="Создание рецепта",
     *     description="Создает новый рецепт. Требуется авторизация по токену (Bearer).",
     *     tags={"Recipes"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","servings","ingredients","steps"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Борщ"),
     *             @OA\Property(property="description", type="string", example="Традиционный украинский борщ"),
     *             @OA\Property(property="servings", type="integer", example=4, description="Количество порций"),
     *             @OA\Property(property="cooking_time", type="integer", nullable=true, example=60, description="Время приготовления в минутах"),
     *             @OA\Property(property="is_published", type="boolean", example=true, description="Опубликован ли рецепт"),
     *
     *             @OA\Property(
     *                 property="ingredients",
     *                 type="array",
     *                 description="Список ингредиентов",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id","weight_grams"},
     *                     @OA\Property(property="id", type="integer", example=12, description="ID ингредиента"),
     *                     @OA\Property(property="weight_grams", type="number", example=200, description="Вес в граммах")
     *                 )
     *             ),
     *
     *             @OA\Property(
     *                 property="steps",
     *                 type="array",
     *                 description="Этапы приготовления",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"description","step_number"},
     *                     @OA\Property(property="description", type="string", example="Нарезать овощи"),
     *                     @OA\Property(property="step_number", type="integer", example=1, description="Порядковый номер шага")
     *                 )
     *             ),
     *
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 description="Теги рецепта",
     *                 @OA\Items(type="integer", example=1)
     *             ),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Успешно"),
     *             @OA\Property(property="data", type="object", description="Созданный рецепт"),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Пользователь не найден (не авторизован)"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Ошибка валидации"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="object",
     *                 example={"title": {"Название рецепта обязательно."}, "ingredients": {"Нужно указать хотя бы один ингредиент."}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Неизвестная ошибка"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"SQLSTATE[HY000]: General error"})
     *         )
     *     )
     * )
     */
    public function createRecipe(RecipeRequest $request) {
        $user = $request->user();

        if (!$user) {
            ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка создания рецепта', ['Request' => $request]);
            return ApiResponse::error(
                'Пользователь не найден (не авторизован)',
                401,
                []
            );
        }

        try {
            $data = $request->validated();
            $recipe = $this->recipeService->createRecipe($data, $user);
            return ApiResponse::success($recipe);
        } catch (\Throwable $exception) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка при создании рецепта', ['Error' => $exception]);
            return ApiResponse::error(
                'Неизвестная ошибка',
                500,
                [$exception->getMessage()]
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/recipes/get-recipe-list",
     *     summary="Получение списка рецептов",
     *     description="Возвращает список рецептов с возможностью фильтрации и пагинации. Требуется авторизация.",
     *     tags={"Recipes"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="is_published",
     *         in="query",
     *         required=false,
     *         description="Фильтр по опубликованным/неопубликованным рецептам",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="author_id",
     *         in="query",
     *         required=false,
     *         description="Фильтр по автору (ID пользователя)",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Parameter(
     *         name="tag_ids[]",
     *         in="query",
     *         required=false,
     *         description="Фильтр по тегам (массив ID тегов)",
     *         @OA\Schema(type="array", @OA\Items(type="integer", example=3))
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Поиск по названию/описанию",
     *         @OA\Schema(type="string", example="борщ")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Количество элементов на странице (по умолчанию 15, макс. 100)",
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Успешно"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Список рецептов с пагинацией",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="title", type="string", example="Борщ"),
     *                         @OA\Property(property="preview_image", type="string", example="https://site.com/storage/recipes/101.jpg"),
     *                         @OA\Property(property="servings", type="integer", example=4),
     *                         @OA\Property(property="cooking_time", type="integer", example=60),
     *                         @OA\Property(property="is_published", type="boolean", example=true),
     *                         @OA\Property(property="author_id", type="integer", example=5),
     *                         @OA\Property(property="tags", type="array", @OA\Items(type="string", example="Супы"))
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=125),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="last_page", type="integer", example=9),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=15)
     *             ),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Пользователь не найден (не авторизован)"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Неизвестная ошибка"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"SQLSTATE[HY000]: General error"})
     *         )
     *     )
     * )
     */
    //Вообще все рецепты
    public function getRecipeList(RecipeListRequest $request)
    {
        $user = $request->user();

        if (!$user) {
            ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка получения рецептов', ['Request' => $request]);
            return ApiResponse::error('Пользователь не найден (не авторизован)', 401, []);
        }

        try {
            $filters = $request->filters();
            $perPage = $request->perPage();

            $recipeList = $this->recipeService->listRecipes($filters, $perPage);

            return ApiResponse::success($recipeList);
        } catch (\Throwable $exception) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка при получении рецептов', ['Error' => $exception]);
            return ApiResponse::error('Неизвестная ошибка', 500, [$exception->getMessage()]);
        }
    }

    /**
     * @OA\Get(
     *     path="/recipes/get-recipe-by-id/{id}",
     *     summary="Получение рецепта по ID",
     *     description="Возвращает рецепт по его идентификатору. Требуется авторизация.",
     *     tags={"Recipes"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID рецепта",
     *         @OA\Schema(type="integer", example=101)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Успешно"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Объект рецепта",
     *                 @OA\Property(property="id", type="integer", example=101),
     *                 @OA\Property(property="title", type="string", example="Борщ"),
     *                 @OA\Property(property="description", type="string", example="Традиционный украинский борщ"),
     *                 @OA\Property(property="preview_image", type="string", example="https://site.com/storage/recipes/101.jpg"),
     *                 @OA\Property(property="servings", type="integer", example=4),
     *                 @OA\Property(property="cooking_time", type="integer", example=60),
     *                 @OA\Property(property="is_published", type="boolean", example=true),
     *                 @OA\Property(property="author_id", type="integer", example=5),
     *                 @OA\Property(
     *                     property="ingredients",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="name", type="string", example="Картофель"),
     *                         @OA\Property(property="weight_grams", type="number", example=200)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="steps",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="step_number", type="integer", example=1),
     *                         @OA\Property(property="description", type="string", example="Нарезать овощи"),
     *                         @OA\Property(property="image", type="string", nullable=true, example="https://site.com/storage/steps/1.jpg")
     *                     )
     *                 ),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="string", example="Супы"))
     *             ),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Пользователь не найден (не авторизован)"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Рецепт не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Рецепт не найден"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items(type="string"),
     *                  example={}
     *              )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Неизвестная ошибка"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"SQLSTATE[HY000]: General error"})
     *         )
     *     )
     * )
     */
    //Рецепт по id
    public function getRecipeById(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка получения рецепта', ['Request' => $request]);
            return ApiResponse::error(
                'Пользователь не найден (не авторизован)',
                401,
                []
            );
        }

        try {
            $recipe = Recipe::find($id);

            if (!$recipe) {
                return ApiResponse::error(
                    'Рецепт не найден',
                    404,
                    []
                );
            }

            $recipe = $this->recipeService->getRecipeById($id);

            return ApiResponse::success($recipe);
        } catch (\Throwable $exception) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка при удалении рецепта', ['Error' => $exception]);
            return ApiResponse::error(
                'Неизвестная ошибка',
                500,
                [$exception->getMessage()]
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/recipes/get-recipe-by-user-id/{id}",
     *     summary="Получение списка рецептов пользователя",
     *     description="Возвращает список рецептов указанного пользователя с возможностью фильтрации и пагинации. Требуется авторизация.",
     *     tags={"Recipes"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID пользователя, чьи рецепты нужно получить",
     *         @OA\Schema(type="integer", example=7)
     *     ),
     *
     *     @OA\Parameter(
     *         name="is_published",
     *         in="query",
     *         required=false,
     *         description="Фильтр по опубликованным/неопубликованным рецептам",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="tag_ids[]",
     *         in="query",
     *         required=false,
     *         description="Фильтр по тегам (массив ID тегов)",
     *         @OA\Schema(type="array", @OA\Items(type="integer", example=3))
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Поиск по названию/описанию",
     *         @OA\Schema(type="string", example="паста")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Количество элементов на странице (по умолчанию 15, макс. 100)",
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Успешно"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Список рецептов пользователя с пагинацией",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=101),
     *                         @OA\Property(property="title", type="string", example="Паста карбонара"),
     *                         @OA\Property(property="preview_image", type="string", example="https://site.com/storage/recipes/101.jpg"),
     *                         @OA\Property(property="servings", type="integer", example=2),
     *                         @OA\Property(property="cooking_time", type="integer", example=25),
     *                         @OA\Property(property="is_published", type="boolean", example=true),
     *                         @OA\Property(property="author_id", type="integer", example=7),
     *                         @OA\Property(property="tags", type="array", @OA\Items(type="string", example="Итальянская кухня"))
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=42),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=15)
     *             ),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Пользователь не найден (не авторизован)"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Неизвестная ошибка"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"SQLSTATE[HY000]: General error"})
     *         )
     *     )
     * )
     */
    //Список рецептов любого юзера
    public function getRecipeByUserId(RecipeListRequest $request, int $id)
    {
        $authUser = $request->user();

        if (!$authUser) {
            ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка получения рецептов пользователя', ['Request' => $request]);
            return ApiResponse::error('Пользователь не найден (не авторизован)', 401, []);
        }

        try {
            // Получаем фильтры и perPage из запроса
            $filters = $request->filters();
            $perPage = $request->perPage();

            // Передаем ID пользователя в сервис
            $recipeList = $this->recipeService->listUserRecipes($id, $filters, $perPage);

            return ApiResponse::success($recipeList);
        } catch (\Throwable $exception) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка при получении рецептов пользователя', ['Error' => $exception]);
            return ApiResponse::error('Неизвестная ошибка', 500, [$exception->getMessage()]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/recipes/delete-recipe/{id}",
     *     summary="Удаление рецепта",
     *     description="Удаляет рецепт по ID. Требуется авторизация.",
     *     tags={"Recipes"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID рецепта для удаления",
     *         @OA\Schema(type="integer", example=101)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Рецепт успешно удалён",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Успешно"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Удалённый рецепт",
     *                 @OA\Property(property="id", type="integer", example=101),
     *                 @OA\Property(property="title", type="string", example="Борщ"),
     *                 @OA\Property(property="is_deleted", type="boolean", example=true, description="Флаг удаления (может возвращаться)")
     *             ),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Пользователь не найден (не авторизован)"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Рецепт не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Рецепт не найден"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Неизвестная ошибка"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"SQLSTATE[HY000]: General error"})
     *         )
     *     )
     * )
     */
    public function deleteRecipe(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка удаления рецепта', ['Request' => $request]);
            return ApiResponse::error(
                'Пользователь не найден (не авторизован)',
                401,
                []
            );
        }

        try {
            $recipe = Recipe::find($id);

            if (!$recipe) {
                return ApiResponse::error(
                    'Рецепт не найден',
                    404,
                    []
                );
            }

            $recipe = $this->recipeService->deleteRecipe($recipe);

            return ApiResponse::success($recipe);
        } catch (\Throwable $exception) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка при удалении рецепта', ['Error' => $exception]);
            return ApiResponse::error(
                'Неизвестная ошибка',
                500,
                [$exception->getMessage()]
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/recipes/update-recipe",
     *     summary="Обновление рецепта",
     *     description="Создает новый рецепт. Требуется авторизация по токену (Bearer).",
     *     tags={"Recipes"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","servings","ingredients","steps"},
     *             @OA\Property(property="id", type="integer", nullable=true, example=1, description="ID рецепта (для обновления)"),
     *             @OA\Property(property="title", type="string", maxLength=255, example="Борщ"),
     *             @OA\Property(property="description", type="string", example="Традиционный украинский борщ"),
     *             @OA\Property(property="servings", type="integer", example=4, description="Количество порций"),
     *             @OA\Property(property="cooking_time", type="integer", nullable=true, example=60, description="Время приготовления в минутах"),
     *             @OA\Property(property="is_published", type="boolean", example=true, description="Опубликован ли рецепт"),
     *
     *             @OA\Property(
     *                 property="ingredients",
     *                 type="array",
     *                 description="Список ингредиентов",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id","weight_grams"},
     *                     @OA\Property(property="id", type="integer", example=12, description="ID ингредиента"),
     *                     @OA\Property(property="weight_grams", type="number", example=200, description="Вес в граммах")
     *                 )
     *             ),
     *
     *             @OA\Property(
     *                 property="steps",
     *                 type="array",
     *                 description="Этапы приготовления",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"description","step_number"},
     *                     @OA\Property(property="description", type="string", example="Нарезать овощи"),
     *                     @OA\Property(property="step_number", type="integer", example=1, description="Порядковый номер шага")
     *                 )
     *             ),
     *
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 description="Теги рецепта",
     *                 @OA\Items(type="integer", example=1)
     *             ),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Успешно"),
     *             @OA\Property(property="data", type="object", description="Созданный рецепт"),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Пользователь не найден (не авторизован)"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Ошибка валидации"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="object",
     *                 example={"title": {"Название рецепта обязательно."}, "ingredients": {"Нужно указать хотя бы один ингредиент."}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Неизвестная ошибка"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), example={"SQLSTATE[HY000]: General error"})
     *         )
     *     )
     * )
     */
    public function updateRecipe(RecipeRequest $request) {
        $user = $request->user();

        if (!$user) {
            ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка создания рецепта', ['Request' => $request]);
            return ApiResponse::error(
                'Пользователь не найден (не авторизован)',
                401,
                []
            );
        }

        try {
            $data = $request->validated();
            if (empty($data['id'])) {
                return ApiResponse::error(
                    'Рецепт не найден',
                    404,
                    []
                );
            }
            $recipe = Recipe::find($data['id']);
            $recipe = $this->recipeService->updateRecipe($recipe, $data);
            return ApiResponse::success($recipe);
        } catch (\Throwable $exception) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка при создании рецепта', ['Error' => $exception]);
            return ApiResponse::error(
                'Неизвестная ошибка',
                500,
                [$exception->getMessage()]
            );
        }
    }
}
