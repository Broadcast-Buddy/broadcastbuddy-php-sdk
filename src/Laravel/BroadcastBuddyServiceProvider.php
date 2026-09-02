<?php

namespace BroadcastBuddy\Laravel;

use BroadcastBuddy\Client;
use Illuminate\Support\ServiceProvider;

class BroadcastBuddyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/broadcastbuddy.php',
            'broadcastbuddy'
        );

        $this->app->singleton('broadcastbuddy', function ($app) {
            $apiKey = config('broadcastbuddy.api_key', env('BROADCAST_BUDDY_API_KEY', ''));
            $timeout = (int) config('broadcastbuddy.timeout', 30);

            return new Client($apiKey, $timeout);
        });

        $this->app->alias('broadcastbuddy', Client::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/broadcastbuddy.php' => config_path('broadcastbuddy.php'),
            ], 'broadcastbuddy-config');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['broadcastbuddy', Client::class];
    }
}
