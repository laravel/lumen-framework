<?php

namespace Laravel\Lumen\Console;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Laravel\Lumen\Application;
use Laravel\Lumen\Exceptions\Handler;
use RuntimeException;
use Throwable;

class Kernel implements KernelContract
{
    /**
     * The application implementation.
     *
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * The Artisan application instance.
     *
     * @var \Illuminate\Console\Application
     */
    protected $artisan;

    /**
     * Indicates if facade aliases are enabled for the console.
     *
     * @var bool
     */
    protected $aliases = true;

    /**
     * The Artisan commands provided by the application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Create a new console kernel instance.
     *
     * @param  \Laravel\Lumen\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        if ($this->app->runningInConsole()) {
            $this->setRequestForConsole($this->app);
        }

        $this->app->prepareForConsoleCommand($this->aliases);
        $this->defineConsoleSchedule();
    }

    /**
     * Set the request instance for URL generation.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    protected function setRequestForConsole(Application $app)
    {
        $uri = $app->make('config')->get('app.url', 'http://localhost');

        $components = parse_url($uri);

        $server = $_SERVER;

        if (isset($components['path'])) {
            $server = array_merge($server, [
                'SCRIPT_FILENAME' => $components['path'],
                'SCRIPT_NAME' => $components['path'],
            ]);
        }

        $app->instance('request', Request::create(
            $uri, 'GET', [], [], [], $server
        ));
    }

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function defineConsoleSchedule()
    {
        $this->app->instance(
            Schedule::class, $schedule = new Schedule
        );

        $this->schedule($schedule);
    }

    /**
     * Run the console application.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    public function handle($input, $output = null)
    {
        try {
            $this->app->boot();

            $status = $this->getArtisan()->run($input, $output);
        } catch (Throwable $e) {
            $this->reportException($e);

            $this->renderException($output, $e);

            $status = 1;
        }

        $this->terminate($input, $status);

        return $status;
    }

    /**
     * Bootstrap the application for artisan commands.
     *
     * @return void
     */
    public function bootstrap()
    {
        //
    }

    /**
     * Terminate the application.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  int  $status
     * @return void
     */
    public function terminate($input, $status)
    {
        $this->app->terminate();
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }

    /**
     * Run an Artisan console command by name.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return int
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        return $this->getArtisan()->call($command, $parameters, $outputBuffer);
    }

    /**
     * Queue the given console command.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return void
     */
    public function queue($command, array $parameters = [])
    {
        throw new RuntimeException('Queueing Artisan commands is not supported by Lumen.');
    }

    /**
     * Get all of the commands registered with the console.
     *
     * @return array
     */
    public function all()
    {
        return $this->getArtisan()->all();
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        return $this->getArtisan()->output();
    }

    /**
     * Get the Artisan application instance.
     *
     * @return \Illuminate\Console\Application
     */
    protected function getArtisan()
    {
        if (is_null($this->artisan)) {
            return $this->artisan = (new Artisan($this->app, $this->app->make('events'), $this->app->version()))
                                ->resolveCommands($this->getCommands())
                                ->setContainerCommandLoader();
        }

        return $this->artisan;
    }

    /**
     * Get the commands to add to the application.
     *
     * @return array
     */
    protected function getCommands()
    {
        return array_merge($this->commands, [
            ScheduleRunCommand::class,
        ]);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function reportException(Throwable $e)
    {
        $this->resolveExceptionHandler()->report($e);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  \Throwable  $e
     * @return void
     */
    protected function renderException($output, Throwable $e)
    {
        $this->resolveExceptionHandler()->renderForConsole($output, $e);
    }

    /**
     * Get the exception handler from the container.
     *
     * @return \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected function resolveExceptionHandler()
    {
        if ($this->app->bound(ExceptionHandler::class)) {
            return $this->app->make(ExceptionHandler::class);
        } else {
            return $this->app->make(Handler::class);
        }
    }
}
