<?php

namespace App\Services;

use App\Enums\LogLevels;
use App\Facades\ClickHouseLog;
use App\Http\Requests\BannerRequest;
use App\Http\Requests\BannerUpdateRequest;
use App\Interfaces\BannerRepositoryInterface;
use App\Models\Banner;
use App\Interfaces\BannerServiceInterface;
use Illuminate\Http\Request;

class RecipeService implements BannerServiceInterface
{
    protected BannerRepositoryInterface $userRepository;

    public function __construct(BannerRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function orchidCreateBanner(BannerUpdateRequest $request): Banner
    {
        try {
            return $this->userRepository->orchidCreateBanner($request);
        } catch (\Throwable $e) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка создания баннера', ['error' => $e->getMessage()]);
            throw $e;
        }

    }

    public function orchidUpdateBanner(BannerUpdateRequest $request): bool
    {
        try {
            return $this->userRepository->orchidUpdateBanner($request);
        } catch (\Throwable $e) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка создания баннера', ['error' => $e->getMessage()]);
            throw $e;
        }

    }

    public function orchidDeleteBanner(Request $request): bool
    {
        try {
            return $this->userRepository->orchidDeleteBanner($request);
        } catch (\Throwable $e) {
            ClickHouseLog::log(LogLevels::ERROR, 'Ошибка создания баннера', ['error' => $e->getMessage()]);
            throw $e;
        }

    }

    public function getBanners(array $request): array
    {
        try {
            return $this->userRepository->getBanners($request);
        } catch (\Throwable $e) {
            ClickHouseLog::log(LogLevels::ERROR, 'Не удалось получить список баннеров', ['Error' => $e->getMessage()]);
            throw $e;
        }
    }

}
