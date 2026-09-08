<?php

namespace App\Providers;

use Laravel\Reverb\ReverbServiceProvider as BaseReverbServiceProvider;

class ReverbServiceProvider extends BaseReverbServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            base_path('vendor/laravel/reverb/config/reverb.php'), 'reverb'
        );

        $this->app->instance(\Laravel\Reverb\Contracts\Logger::class, new \Laravel\Reverb\Loggers\NullLogger);

        $this->app->singleton(\Laravel\Reverb\ServerProviderManager::class);

        $this->app->make(\Laravel\Reverb\ServerProviderManager::class)->register();
    }
}
