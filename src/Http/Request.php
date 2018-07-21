<?php

namespace Laravel\Lumen\Http;

use Illuminate\Http\Request as BaseRequest;

class Request extends BaseRequest
{

    /**
     * Get the route handling the request.
     *
     * @param  string|null  $param
     *
     * @return \Illuminate\Routing\Route|object|string
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