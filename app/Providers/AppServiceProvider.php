<?php

namespace App\Providers;

use App\Exceptions\UnsupportedPlatformException;
use App\Services\OAuthService;
use App\Services\ShopifyOAuthService;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OAuthService::class, function ($app) {
            return match (request()->route('platform')) {
                'shopify' => new ShopifyOAuthService(),
                default => throw new UnsupportedPlatformException(request()->route('platform'))
            };
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
