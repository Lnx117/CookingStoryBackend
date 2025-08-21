<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Orchid\Attachment\Engines\Generator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(\App\Providers\AuthCustomServiceProvider::class);
        $this->app->register(\App\Providers\BannerServiceProvider::class);
        $this->app->register(\App\Providers\FileStoreServiceProvider::class);
        $this->app->register(\App\Providers\RecipeServiceProvider::class);
        $this->app->register(\App\Providers\RecipeIngredientProvider::class);
        $this->app->register(\App\Providers\RecipeStepProvider::class);
        $this->app->register(\App\Providers\RecipeTagProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
