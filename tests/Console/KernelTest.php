<?php

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Photon\Application;
use Photon\Console\Kernel as ConsoleKernel;
use Photon\Exceptions\Handler as ExceptionHandler;

class KernelTest extends \Photon\Testing\TestCase
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

        $app->singleton(ExceptionHandlerContract::class, fn () => new ExceptionHandler());
        $app->singleton(ConsoleKernelContract::class, function () use ($app) {
            return tap(new ConsoleKernel($app), function ($kernel) {
                $kernel->rerouteSymfonyCommandEvents();
            });
        });

        return $app;
    }

    public function testItCanRerouteToSymfonyEvent()
    {
        $this->expectsEvents([CommandStarting::class, CommandFinished::class]);

        $this->artisan('cache:forget', ['key' => 'photon']);
    }
}
