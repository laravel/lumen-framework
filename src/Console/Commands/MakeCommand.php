<?php namespace Laravel\Lumen\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class MakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold various parts of your application';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        switch ($this->argument('name')) {
            case 'foundation':
                $this->makeDatabase();
                $this->makeResources();
                break;

            case 'database':
                $this->makeDatabase();
                break;

            case 'lang':
                $this->makeLang();
                break;

            case 'resources':
                $this->makeResources();
                break;

            case 'views':
                $this->makeViews();
                break;

            default:
                throw new \InvalidArgumentException;
        }

        $this->info('Directories created!');
    }

    /**
     * Make the database directory structure.
     *
     * @return void
     */
    public function makeDatabase()
    {
        $this->makeSeeds();

        mkdir(base_path('database/migrations'), 0755, true);
        touch(base_path('database/migrations/.gitkeep'));
    }

    /**
     * Make the database seeds directory structure.
     *
     * @return void
     */
    protected function makeSeeds()
    {
        mkdir(base_path('database/seeds'), 0755, true);

        if (! file_exists($seedPath = base_path('database/seeds/DatabaseSeeder.php'))) {
            copy(__DIR__.'/stubs/DatabaseSeeder.stub', $seedPath);
        }
    }

    /**
     * Make the full "resources" directory structure.
     *
     * @return void
     */
    public function makeResources()
    {
        $this->makeLang();

        $this->makeViews();
    }

    /**
     * Make the "lang" directory structure.
     *
     * @return void
     */
    public function makeLang()
    {
        mkdir(base_path('resources/lang/en'), 0755, true);

        if (! file_exists(base_path($langFile = 'resources/lang/en/validation.php'))) {
            copy(__DIR__.'/../../../lang/en/validation.php', $langFile);
        }
    }

    /**
     * Make the full "views" directory structure.
     *
     * @return void
     */
    public function makeViews()
    {
        mkdir(base_path('resources/views'), 0755, true);

        touch(base_path('resources/views/.gitkeep'));
    }

    /**
     * Write a .gitignore file to the given directory.
     *
     * @param  string  $path
     * @return void
     */
    protected function writeGitIgnoreFile($path)
    {
        file_put_contents($path.'/.gitignore', '*'.PHP_EOL.'!.gitignore'.PHP_EOL);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The section of the application to scaffold'],
        ];
    }
}
