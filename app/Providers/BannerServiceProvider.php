<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\BannerRepository;
use App\Services\BannerService;
use App\Interfaces\BannerRepositoryInterface;
use App\Interfaces\BannerServiceInterface;

class BannerServiceProvider extends ServiceProvider
{
    /**
    * Register services.
    *
    * @return void
    */
    public function register()
    {
        $this->app->singleton(BannerRepositoryInterface::class, BannerRepository::class);

        $this->app->singleton(BannerServiceInterface::class, BannerService::class);
    }

    /**
    * Bootstrap services.
    *
    * @return void
    */
    public function boot()
    {
        //
    }
}
