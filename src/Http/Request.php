<?php

namespace Laravel\Lumen\Http;

use Illuminate\Http\Request as BaseRequest;
use Illuminate\Support\Arr;

class Request extends BaseRequest
{
    /**
     * Get the route handling the request.
     *
     * @param  string|null  $param
     *
     * @return array|string
     */
    public function route($param = null)
    {
        $route = call_user_func($this->getRouteResolver());

        if (is_null($route) || is_null($param)) {
            return $route;
        }

        return Arr::get($route[2], $param);
    }
}