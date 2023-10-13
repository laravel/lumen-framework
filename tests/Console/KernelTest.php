<?php

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Support\Facades\Event;
use Laravel\Lumen\Application;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class KernelTest extends \Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $app = new Application();

        $app->configure('app');
        $app->configure('events');

        $app->singleton(ExceptionHandlerContract::class, fn () => new ExceptionHandler());
        $app->singleton(ConsoleKernelContract::class, function () use ($app) {
            return tap(new ConsoleKernel($app, $app['events']), function ($kernel) {
                $kernel->rerouteSymfonyCommandEvents();
            });
        });

        return $app;
    }

    public function testItCanRerouteToSymfonyEvent()
    {
        $this->expectsEvents('Illuminate\Console\Events\CommandStarting');
        $this->expectsEvents('Illuminate\Console\Events\CommandFinished');

        $this->artisan('cache:forget', ['key' => 'lumen']);
    }
}
