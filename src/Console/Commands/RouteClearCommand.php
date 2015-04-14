<?php namespace Laravel\Lumen\Console\Commands;

use Illuminate\Console\Command;

class RouteClearCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the route cache file';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if (file_exists($this->laravel->getCachedRoutesPath())) {
            @unlink($this->laravel->getCachedRoutesPath());
        }

        $this->info('Route cache cleared!');
    }
}
