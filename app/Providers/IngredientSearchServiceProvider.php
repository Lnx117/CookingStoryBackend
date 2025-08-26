<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\IngredientSearchServiceInterface;
use App\Services\IngredientSearchService;

class IngredientSearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            IngredientSearchServiceInterface::class,
            IngredientSearchService::class
        );
    }
}
