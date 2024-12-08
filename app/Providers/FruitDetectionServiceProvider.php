<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\FruitDetectionServiceInterface;
use App\Services\FruitDetectionService;

class FruitDetectionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FruitDetectionServiceInterface::class, FruitDetectionService::class);
    }
}