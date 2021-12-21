<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\CacheRepositoryInterface;
use App\Repositories\CacheRepository;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->bind(CacheRepositoryInterface::class, CacheRepository::class);
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
