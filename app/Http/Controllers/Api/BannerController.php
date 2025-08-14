<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\BannerRequest;
use App\Http\Responses\ApiResponse;
use App\Interfaces\BannerServiceInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class BannerController extends Controller
{
    protected $bannerService;

    public function __construct(BannerServiceInterface $bannerService){
        $this->bannerService = $bannerService;
    }
    /**
     * Метод для получения данных баннеров
     *
     * @param BannerRequest $request
     * @return \Illuminate\Http\JsonResponse
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
