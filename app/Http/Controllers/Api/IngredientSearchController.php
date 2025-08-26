<?php

namespace App\Http\Controllers\Api;

use App\Enums\LogLevels;
use App\Facades\ClickHouseLog;
use App\Http\Responses\ApiResponse;
use App\Interfaces\IngredientSearchServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class IngredientSearchController extends Controller
{
    public function __construct(
        private IngredientSearchServiceInterface $searchService
    ) {}

    /**
     * Поиск ингредиентов по части слова.
     *
     * @OA\Get(
     *     path="/api/ingredients/search",
     *     summary="Поиск ингредиентов",
     *     description="Возвращает список ингредиентов по части слова (использует Elasticsearch).",
     *     tags={"Ingredients"},
     *
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
     *         description="Поисковый запрос (часть названия ингредиента)",
     *         @OA\Schema(type="string", example="майо")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Максимальное количество результатов (по умолчанию 10)",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Успешно"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=12),
     *                     @OA\Property(property="name", type="string", example="Майонез")
     *                 )
     *             ),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка запроса",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Ошибка"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="object", nullable=true, example={"q": "Поле обязательно"})
     *         )
     *     )
     * )
     */
    public function __invoke(Request $request)
    {
        try {
            $query = $request->query('q', '');
            $limit = (int) $request->query('limit', 10);

            if (empty($query)) {
                return ApiResponse::error('Параметр q обязателен', 400, ['q' => 'Поле обязательно']);
            }

            $data = $this->searchService->search($query, $limit);

            return ApiResponse::success($data);
        } catch (\Throwable $exception) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка при поиске ингредиента через ES', ['Error' => $exception]);
            return ApiResponse::error(
                'Ошибка при поиске ингредиента через ES',
                500,
                [$exception->getMessage()]
            );
        }

    }
}
