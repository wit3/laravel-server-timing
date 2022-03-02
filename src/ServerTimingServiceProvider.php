<?php

namespace BeyondCode\ServerTiming;

use Illuminate\Support\ServiceProvider;

class ServerTimingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

        if (isset($_SERVER['LARAVEL_OCTANE'])) {
            $this->setupOctane();
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->singleton(ServerTiming::class, function ($app) {
            return new ServerTiming(new \Symfony\Component\Stopwatch\Stopwatch());
        });
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('timing.php'),
        ], 'server-timing-config');
    }

    protected function resetServerTiming(): void
    {
        /**
         * @var ServerTiming $serverTiming
         */
        $serverTiming = $this->app->get(ServerTiming::class);
        $serverTiming->reset();
    }

    protected function setupOctane(): void
    {
        /** @phpstan-ignore-next-line */
        $this->app['events']->listen(\Laravel\Octane\Events\RequestReceived::class, function () {
            $this->resetServerTiming();
        });

        /** @phpstan-ignore-next-line */
        $this->app['events']->listen(\Laravel\Octane\Events\TaskReceived::class, function () {
            $this->resetServerTiming();
        });

        /** @phpstan-ignore-next-line */
        $this->app['events']->listen(\Laravel\Octane\Events\TickReceived::class, function () {
            $this->resetServerTiming();
        });
    }
}
