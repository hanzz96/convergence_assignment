<?php

namespace App\Providers;

use App\Libraries\Api\Strapi\StrapiApi;
use App\Services\StrapiServices;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         $this->app->singleton(StrapiServices::class, function ($app) {
            return new StrapiServices(new StrapiApi());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
