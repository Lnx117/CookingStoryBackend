<?php
namespace App\Providers;

use App\Interfaces\RecipeIngredientRepositoryInterface;
use App\Interfaces\RecipeIngredientServiceInterface;
use App\Repositories\RecipeIngredientRepository;
use App\Services\RecipeIngredientService;
use Illuminate\Support\ServiceProvider;


class RecipeIngredientProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RecipeIngredientRepositoryInterface::class, RecipeIngredientRepository::class);

        $this->app->singleton(RecipeIngredientServiceInterface::class, RecipeIngredientService::class);
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
