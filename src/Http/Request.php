<?php

namespace Laravel\Lumen\Http;

use Illuminate\Support\Arr;
use Illuminate\Http\Request as BaseRequest;

class Request extends BaseRequest
{
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
}
