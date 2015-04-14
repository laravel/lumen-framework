<?php namespace Laravel\Lumen\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\RouteCollection;

class RouteCacheCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a route cache file for faster route registration';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->call('route:clear');

        $routes = $this->getFreshApplicationRoutes();

        if (count($routes) == 0) {
            return $this->error("Your application doesn't have any routes.");
        }

        file_put_contents($this->laravel->getCachedRoutesPath(), $this->buildRouteCacheFile($routes));

        $this->info('Routes cached successfully!');
    }

    /**
     * Boot a fresh copy of the application and get the routes.
     *
     * @return array
     */
    protected function getFreshApplicationRoutes()
    {
        $app = require $this->laravel->basePath().'/bootstrap/app.php';

        return $app->getRoutes();
    }

    /**
     * Build the route cache file.
     *
     * @param  array  $routes
     * @return string
     */
    protected function buildRouteCacheFile($routes)
    {
        $stub = file_get_contents(__DIR__.'/stubs/routes.stub');

        return str_replace('{{routes}}', base64_encode(serialize($routes)), $stub);
    }
}
