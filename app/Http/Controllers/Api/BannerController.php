<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\BannerRequest;
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
        return $this->bannerService->getBanners($request);
    }

}
