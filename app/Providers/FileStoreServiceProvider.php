<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\FileStoreService;
use App\Interfaces\FileStoreServiceInterface;

class FileStoreServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(FileStoreServiceInterface::class, FileStoreService::class);
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
