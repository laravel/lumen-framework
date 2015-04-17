<?php namespace Laravel\Lumen\Routing;

use Illuminate\Http\Request;

abstract class Controller
{
    use DispatchesCommands, ValidatesRequests;

    /**
     * The middleware defined on the controller.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Define a middleware on the controller.
     *
     * @param  string  $middleware
     * @param  array  $options
     * @return void
     */
    public function middleware($middleware, array $options = array())
    {
        $this->middleware[$middleware] = $options;
    }

    /**
     * Get the middleware for a given method.
     *
     * @param  Request  $request
     * @param  string  $method
     * @return array
     */
    public function getMiddlewareForMethod(Request $request, $method)
    {
        $middleware = [];

        foreach ($this->middleware as $name => $options) {
            if (isset($options['only']) && ! in_array($method, (array) $options['only'])) {
                continue;
            }

            if (isset($options['except']) && in_array($method, (array) $options['except'])) {
                continue;
            }

            $middleware[] = $name;
        }

        return $middleware;
    }
}
