<?php

namespace Laravel\Lumen\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Illuminate\Support\Env;
use Symfony\Component\Console\Output\ConsoleOutput;

class LoadEnvironmentVariables
{
    /**
     * The paths where to look for the environment file(s).
     *
     * @var string|string[]
     */
    protected $paths;

    /**
     * The name(s) of the environment file(s).
     * If 'null', the default file name '.env' will be used.
     * Default: null.
     *
     * @var string|string[]|null
     */
    protected $names;

    /**
     * Should file loading short circuit?
     * Default: true.
     *
     * @var bool
     */
    protected $shortCircuit;

    /**
     * The file encoding.
     * Default: null.
     *
     * @var string|null
     */
    protected $fileEncoding;

    /**
     * Create a new loads environment variables instance.
     *
     * @param  string|string[]       $paths
     * @param  string|string[]|null  $names
     * @param  bool                  $shortCircuit
     * @param  string|null           $fileEncoding
     *
     * @return void
     */
    public function __construct(
        $paths,
        $names = null,
        $shortCircuit = true,
        $fileEncoding = null
    ) {
        $this->paths = $paths;
        $this->names = $names;
        $this->shortCircuit = $shortCircuit;
        $this->fileEncoding = $fileEncoding;
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
            $this->paths,
            $this->names,
            $this->shortCircuit,
            $this->fileEncoding,
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

        exit(1);
    }
}
