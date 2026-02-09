<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use App\Models\StoreSetting;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\CustomerStatsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment(['local', 'production'])) {
            URL::forceScheme('https');
        }
        Order::observe(OrderObserver::class);
        try {
            // Only run if store_settings table exists (to avoid migration errors)
            if (Schema::hasTable('store_settings')) {
                $settings = StoreSetting::first();

                if ($settings && $settings->timezone) {
                    // Set application timezone globally
                    Config::set('app.timezone', $settings->timezone);
                    date_default_timezone_set($settings->timezone);
                }
            }
        } catch (\Exception $e) {
            // Silent fail - use default timezone from config/app.php
            \Log::warning('Could not set store timezone: ' . $e->getMessage());
        }
    }
}
