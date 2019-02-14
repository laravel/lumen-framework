<?php

namespace Laravel\Lumen\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Environment\DotenvFactory;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
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
     * @param  string|null  $name
     * @return void
     */
    public function __construct($path, $name = null)
    {
        $this->filePath = $path;
        $this->fileName = $name;
    }

    /**
     * Setuup the environment variables.
     *
     * If no environment file exists, we continue silently.
     *
     * @return void
     */
    public function bootstrap()
    {
        if (! class_exists(DotenvFactory::class)) {
            $this->writeErrorAndDie([
                'Unable to load the environment file!',
                'Please install "vlucas/phpdotenv:^3.3".',
            ]);
        }

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
            $this->filePath,
            $this->fileName,
            new DotenvFactory([new EnvConstAdapter, new ServerConstAdapter])
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
}
