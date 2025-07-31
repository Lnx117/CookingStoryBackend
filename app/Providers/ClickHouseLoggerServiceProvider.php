<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ClickHouseLogger;

class ClickHouseLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ClickHouseLogger::class, function ($app) {
            return new ClickHouseLogger();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
