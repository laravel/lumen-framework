<?php

namespace Laravel\Lumen\Routing;

use Closure as BaseClosure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Validator;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Validation\ValidationException;

trait ProvidesConvenienceMethods
{
    /**
     * Set the response builder callback.
     *
     * @deprecated
     * @param  \Closure  $callback
     * @return void
     */
    public static function buildResponseUsing(BaseClosure $callback)
    {
        app()->buildResponseUsing($callback);
    }

    /**
     * Set the error formatter callback.
     *
     * @deprecated
     * @param  \Closure  $callback
     * @return void
     */
    public static function formatErrorsUsing(BaseClosure $callback)
    {
        app()->formatErrorsUsing($callback);
    }

    /**
     * Validate the given request with the given rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        return $validator->getData();
    }

    /**
     * Throw the failed validation exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function throwValidationException(Request $request, $validator)
    {
        throw new ValidationException($validator, $this->buildFailedValidationResponse(
            $request, $this->formatValidationErrors($validator)
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        if ($this->getResponseBuilder()) {
            return call_user_func($this->getResponseBuilder(), $request, $errors);
        }

        return new JsonResponse($errors, 422);
    }

    /**
     * {@inheritdoc}
     */
    protected function formatValidationErrors(Validator $validator)
    {
        if ($this->getErrorFormatter()) {
            return call_user_func($this->getErrorFormatter(), $validator);
        }

        return $validator->errors()->getMessages();
    }

    /**
     * Authorize a given action against a set of arguments.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        list($ability, $arguments) = $this->parseAbilityAndArguments($ability, $arguments);

        return app(Gate::class)->authorize($ability, $arguments);
    }

    /**
     * Authorize a given action for a user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|mixed  $user
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return \Illuminate\Auth\Access\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeForUser($user, $ability, $arguments = [])
    {
        list($ability, $arguments) = $this->parseAbilityAndArguments($ability, $arguments);

        return app(Gate::class)->forUser($user)->authorize($ability, $arguments);
    }

    /**
     * Guesses the ability's name if it wasn't provided.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return array
     */
    protected function parseAbilityAndArguments($ability, $arguments)
    {
        if (is_string($ability)) {
            return [$ability, $arguments];
        }

        return [debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'], $ability];
    }

    /**
     * Dispatch a job to its appropriate handler.
     *
     * @param  mixed  $job
     * @return mixed
     */
    public function dispatch($job)
    {
        return app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($job);
    }

    /**
     * Dispatch a command to its appropriate handler in the current process.
     *
     * @param  mixed  $job
     * @param  mixed  $handler
     * @return mixed
     */
    public function dispatchNow($job, $handler = null)
    {
        return app('Illuminate\Contracts\Bus\Dispatcher')->dispatchNow($job, $handler);
    }

    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationFactory()
    {
        return app('validator');
    }

    /**
     * Get a response builder.
     *
     * @return callable|null
     */
    protected function getResponseBuilder()
    {
        return app()->has('routing.responseBuilder') ? app('routing.responseBuilder') : null;
    }

    /**
     * Get a error formatter.
     *
     * @return callable|null
     */
    protected function getErrorFormatter()
    {
        return app()->has('routing.errorFormatter') ? app('routing.errorFormatter') : null;
    }
}
