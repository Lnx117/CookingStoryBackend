<?php
namespace App\Providers;

use App\Interfaces\RecipeRepositoryInterface;
use App\Interfaces\RecipeServiceInterface;
use App\Repositories\RecipeRepository;
use App\Services\RecipeService;
use Illuminate\Support\ServiceProvider;


class RecipeProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RecipeRepositoryInterface::class, RecipeRepository::class);

        $this->app->singleton(RecipeServiceInterface::class, RecipeService::class);
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
