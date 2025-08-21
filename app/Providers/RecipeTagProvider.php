<?php
namespace App\Providers;

use App\Interfaces\RecipeTagRepositoryInterface;
use App\Interfaces\RecipeTagServiceInterface;
use App\Repositories\RecipeTagRepository;
use App\Services\RecipeTagService;
use Illuminate\Support\ServiceProvider;


class RecipeTagProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RecipeTagRepositoryInterface::class, RecipeTagRepository::class);

        $this->app->singleton(RecipeTagServiceInterface::class, RecipeTagService::class);
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
