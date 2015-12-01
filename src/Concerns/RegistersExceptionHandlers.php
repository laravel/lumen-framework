<?php

namespace Laravel\Lumen\Concerns;

use Error;
use Exception;
use ErrorException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;

trait RegistersExceptionHandlers
{
    /**
     * Set the error handling for the application.
     *
     * @return void
     */
    protected function registerErrorHandling()
    {
        error_reporting(-1);

        set_error_handler(function ($level, $message, $file = '', $line = 0) {
            if (error_reporting() & $level) {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
        });

        set_exception_handler(function ($e) {
            $this->handleUncaughtException($e);
        });

        register_shutdown_function(function () {
            $this->handleShutdown();
        });
    }

    /**
     * Handle the application shutdown routine.
     *
     * @return void
     */
    protected function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatalError($error['type'])) {
            $this->handleUncaughtException(new FatalErrorException(
                $error['message'], $error['type'], 0, $error['file'], $error['line']
            ));
        }
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int  $type
     * @return bool
     */
    protected function isFatalError($type)
    {
        $errorCodes = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];

        if (defined('FATAL_ERROR')) {
            $errorCodes[] = FATAL_ERROR;
        }

        return in_array($type, $errorCodes);
    }

    /**
     * Send the exception to the handler and return the response.
     *
     * @param  \Throwable  $e
     * @return Response
     */
    protected function sendExceptionToHandler($e)
    {
        $handler = $this->resolveExceptionHandler();

        if ($e instanceof Error) {
            $e = new FatalThrowableError($e);
        }

        $handler->report($e);

        return $handler->render($this->make('request'), $e);
    }

    /**
     * Handle an uncaught exception instance.
     *
     * @param  \Throwable  $e
     * @return void
     */
    protected function handleUncaughtException($e)
    {
        $handler = $this->resolveExceptionHandler();

        if ($e instanceof Error) {
            $e = new FatalThrowableError($e);
        }

        $handler->report($e);

        if ($this->runningInConsole()) {
            $handler->renderForConsole(new ConsoleOutput, $e);
        } else {
            $handler->render($this->make('request'), $e)->send();
        }
    }

    /**
     * Get the exception handler from the container.
     *
     * @return mixed
     */
    protected function resolveExceptionHandler()
    {
        return $this->make('Illuminate\Contracts\Debug\ExceptionHandler');
    }
}
