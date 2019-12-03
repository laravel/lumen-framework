<?php

namespace Laravel\Lumen\Routing;

use Closure as BaseClosure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline as BasePipeline;
use Throwable;

/**
 * This extended pipeline catches any exceptions that occur during each slice.
 *
 * The exceptions are converted to HTTP responses for proper middleware handling.
 */
class Pipeline extends BasePipeline
{
    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return \Closure
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    $slice = parent::carry();

                    return call_user_func($slice($stack, $pipe), $passable);
                } catch (Throwable $e) {
                    return $this->handleException($passable, $e);
                }
            };
        };
    }

    /**
     * Get the initial slice to begin the stack call.
     *
     * @param  \Closure  $destination
     * @return \Closure
     */
    protected function prepareDestination(BaseClosure $destination)
    {
        return function ($passable) use ($destination) {
            try {
                return call_user_func($destination, $passable);
            } catch (Throwable $e) {
                return $this->handleException($passable, $e);
            }
        };
    }

    /**
     * Handle the given exception.
     *
     * @param  mixed  $passable
     * @param  \Throwable  $e
     * @return mixed
     */
    protected function handleException($passable, Throwable $e)
    {
        if (! $this->container->bound(ExceptionHandler::class) || ! $passable instanceof Request) {
            throw $e;
        }

        $handler = $this->container->make(ExceptionHandler::class);

        $handler->report($e);

        return $handler->render($passable, $e);
    }
}
