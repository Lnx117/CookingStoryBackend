<?php
namespace App\Providers;

use App\Interfaces\RecipeStepRepositoryInterface;
use App\Interfaces\RecipeStepServiceInterface;
use App\Repositories\RecipeStepRepository;
use App\Services\RecipeStepService;
use Illuminate\Support\ServiceProvider;


class RecipeStepProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RecipeStepRepositoryInterface::class, RecipeStepRepository::class);

        $this->app->singleton(RecipeStepServiceInterface::class, RecipeStepService::class);
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
