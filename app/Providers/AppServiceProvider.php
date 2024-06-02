<?php

namespace App\Providers;

use App\Services\Door\DoorControllerInterface;
use App\Services\Door\GpioDoorController;
use App\Services\Door\MockDoorController;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DoorControllerInterface::class, function () {
            return match (config('rabbitmq.door_driver', env('DOOR_DRIVER', 'mock'))) {
                'gpio' => new GpioDoorController,
                default => new MockDoorController,
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
