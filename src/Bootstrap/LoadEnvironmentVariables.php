<?php

namespace Laravel\Lumen\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Illuminate\Support\Env;
use Laravel\Lumen\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class LoadEnvironmentVariables
{
    /**
     * The directory containing the environment file.
     *
     * @var string
     */
    protected $filePath;

    /**
     * The name of the environment file.
     *
     * @var string|null
     */
    protected $fileName;

    /**
     * Create a new loads environment variables instance.
     *
     * @param  string  $path
     * @param  string  $name
     * @return void
     */
    public function __construct($path, $name = '.env')
    {
        $this->filePath = $path;
        $this->fileName = $name;
    }

    /**
     * Setup the environment variables.
     *
     * If no environment file exists, we continue silently.
     *
     * @return void
     */
    public function bootstrap()
    {
        $this->checkForSpecificEnvironmentFile();

        try {
            $this->createDotenv()->safeLoad();
        } catch (InvalidFileException $e) {
            $this->writeErrorAndDie([
                'The environment file is invalid!',
                $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a Dotenv instance.
     *
     * @return \Dotenv\Dotenv
     */
    protected function createDotenv()
    {
        return Dotenv::create(
            Env::getRepository(),
            $this->filePath,
            $this->fileName
        );
    }

    /**
     * Write the error information to the screen and exit.
     *
     * @param  string[]  $errors
     * @return void
     */
    protected function writeErrorAndDie(array $errors)
    {
        $output = (new ConsoleOutput)->getErrorOutput();

        foreach ($errors as $error) {
            $output->writeln($error);
        }

        die(1);
    }

    /**
     * Detect if a custom environment file matching the APP_ENV exists.
     *
     * @return void
     */
    protected function checkForSpecificEnvironmentFile()
    {
        if (Application::isRunningInConsole() && ($input = new ArgvInput)->hasParameterOption('--env')) {
            if ($this->setEnvironmentFilePath(
                $this->fileName.'.'.$input->getParameterOption('--env')
            )) {
                return;
            }
        }

        $environment = Env::get('APP_ENV');

        if (! $environment) {
            return;
        }

        $this->setEnvironmentFilePath(
            $this->fileName.'.'.$environment
        );
    }

    /**
     * Load a custom environment file.
     *
     * @param  string  $file
     * @return bool
     */
    protected function setEnvironmentFilePath($file)
    {
        if (file_exists($this->filePath.'/'.$file)) {
            $this->fileName = $file;

            return true;
        }

        return false;
    }
}
