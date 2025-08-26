<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\BannerRequest;
use App\Http\Responses\ApiResponse;
use App\Interfaces\BannerServiceInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="Banners",
 *     description="API для работы с баннерами"
 * )
 */
class BannerController extends Controller
{
    protected $bannerService;

    public function __construct(BannerServiceInterface $bannerService){
        $this->bannerService = $bannerService;
    }

    /**
     * Метод для получения данных баннеров
     *
     * @OA\Get(
     *     path="/getBanners",
     *     tags={"Banners"},
     *     summary="Получение списка баннеров",
     *     description="Возвращает список баннеров для фронтенда",
     *     operationId="getBanners",
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="Пример фильтра для баннеров",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список баннеров успешно получен",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Успешно"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Баннеры сгруппированные по коду",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Баннер 1"),
     *                         @OA\Property(property="image_url", type="string", example="https://example.com/banner1.jpg"),
     *                         @OA\Property(property="link", type="string", example="https://example.com")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка запроса",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Ошибка"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(type="string", example="Some error message")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Неизвестная ошибка",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Неизвестная ошибка"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(type="string", example="Some error message")
     *             )
     *         )
     *     )
     * )
     */
    public function index(BannerRequest $request): JsonResponse
    {
        try {
            $banners = $this->bannerService->getBanners($request->validated());

            return ApiResponse::success($banners);
        } catch (\Throwable $e) {
            return ApiResponse::error(
                'Неизвестная ошибка',
                500,
                [$e->getMessage()]
            );
        }
    }

}
