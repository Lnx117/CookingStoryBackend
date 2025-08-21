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

    public function index() {
        $recipes = Recipe::all();
    }

    public function createRecipe(RecipeRequest $request) {
        $user = $request->user();

        if (!$user) {
            ClickHouseLog::log(LogLevels::ERROR, 'Неудачная попытка обновления рецепта', ['Request' => $request]);
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
