<?php
namespace App\Interfaces;

use App\Http\Requests\BannerRequest;
use App\Http\Requests\BannerUpdateRequest;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

interface BannerServiceInterface
{
    public function orchidCreateBanner(BannerUpdateRequest $request): Banner;
    public function orchidUpdateBanner(BannerUpdateRequest $request): bool;
    public function orchidDeleteBanner(Request $request): bool;
    public function getBanners(BannerRequest $request): JsonResponse;

}
