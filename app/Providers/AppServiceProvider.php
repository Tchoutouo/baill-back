<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MobileMoneyService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(MobileMoneyService::class, function ($app) {
            return new MobileMoneyService();
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
