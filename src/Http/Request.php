<?php

namespace Laravel\Lumen\Http;

use RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request as BaseRequest;

class Request extends BaseRequest
{
    /**
     * Determine if the route name matches a given pattern.
     *
     * @param  mixed  $patterns
     * @return bool
     */
    public function routeIs(...$patterns)
    {
        if (! Arr::exists($route = $this->route()[1], 'as')) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $route['as'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the route handling the request.
     *
     * @param  string|null  $param
     * @param  mixed  $default
     *
     * @return array|string
     */
    public function route($param = null, $default = null)
    {
        $route = call_user_func($this->getRouteResolver());

        if (is_null($route) || is_null($param)) {
            return $route;
        }

        return Arr::get($route[2], $param, $default);
    }

    /**
     * Get a unique fingerprint for the request / route / IP address.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function fingerprint()
    {
        if (! $route = $this->route()) {
            throw new RuntimeException('Unable to generate fingerprint. Route unavailable.');
        }

        return sha1(implode('|', [
            $this->getMethod(), $this->root(), $this->path(), $this->ip(),
        ]));
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return Arr::has(
            $this->all() + $this->route()[2],
            $offset
        );
    }
}
