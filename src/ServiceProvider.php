<?php

namespace Webparking\DbRebuild;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Webparking\DbRebuild\Commands\DbRebuild;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/db-rebuild.php',
            'db-rebuild'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/db-rebuild.php' => config_path('db-rebuild.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DbRebuild::class,
            ]);
        }
    }
}
