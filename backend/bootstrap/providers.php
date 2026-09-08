<?php

use App\Providers\AppServiceProvider;
use App\Providers\ReverbServiceProvider;
use Laravel\Reverb\ApplicationManagerServiceProvider;

return [
    AppServiceProvider::class,
    ReverbServiceProvider::class,
    ApplicationManagerServiceProvider::class,
];
