<?php

namespace Laravel\Lumen\Routing;

use Closure;
use Exception;
use Throwable;
use FastRoute\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Http\Exception\HttpResponseException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

trait RoutesRequests
{
    /**
     * All of the routes waiting to be registered.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * All of the named routes and URI pairs.
     *
     * @var array
     */
    public $namedRoutes = [];

    /**
     * All of the global middleware for the application.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * All of the route specific middleware short-hands.
     *
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * The current route being dispatched.
     *
     * @var array
     */
    protected $currentRoute;

    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function get($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);

        return $this;
    }

    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);

        return $this;
    }

    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function put($uri, $action)
    {
        $this->addRoute('PUT', $uri, $action);

        return $this;
    }

    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function patch($uri, $action)
    {
        $this->addRoute('PATCH', $uri, $action);

        return $this;
    }

    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function delete($uri, $action)
    {
        $this->addRoute('DELETE', $uri, $action);

        return $this;
    }

    /**
     * Register a route with the application.
     *
     * @param  string  $uri
     * @param  mixed  $action
     * @return $this
     */
    public function options($uri, $action)
    {
        $this->addRoute('OPTIONS', $uri, $action);

        return $this;
    }

    /**
     * Add a route to the collection.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  mixed  $action
     */
    public function addRoute($method, $uri, $action)
    {
        $action = $this->parseAction($action);

        $uri = '/'.trim($uri, '/');

        if (isset($action['as'])) {
            $this->namedRoutes[$action['as']] = $uri;
        }

        $this->routes[$method.$uri] = ['method' => $method, 'uri' => $uri, 'action' => $action];
    }

    /**
     * Parse the action into an array format.
     *
     * @param  mixed  $action
     * @return array
     */
    protected function parseAction($action)
    {
        if (is_string($action)) {
            return ['uses' => $action];
        } elseif (! is_array($action)) {
            return [$action];
        }

        return $action;
    }

    /**
     * Add new middleware to the application.
     *
     * @param  array  $middleware
     * @return $this
     */
    public function middleware(array $middleware)
    {
        $this->middleware = array_unique(array_merge($this->middleware, $middleware));

        return $this;
    }

    /**
     * Define the route middleware for the application.
     *
     * @param  array  $middleware
     * @return $this
     */
    public function routeMiddleware(array $middleware)
    {
        $this->routeMiddleware = array_merge($this->routeMiddleware, $middleware);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(SymfonyRequest $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        return $this->dispatch($request);
    }

    /**
     * Run the application and send the response.
     *
     * @param  SymfonyRequest|null  $request
     * @return void
     */
    public function run($request = null)
    {
        $response = $this->dispatch($request);

        if ($response instanceof SymfonyResponse) {
            $response->send();
        } else {
            echo (string) $response;
        }

        if (count($this->middleware) > 0) {
            $this->callTerminableMiddleware($response);
        }
    }

    /**
     * Call the terminable middleware.
     *
     * @param  mixed  $response
     * @return void
     */
    protected function callTerminableMiddleware($response)
    {
        $response = $this->prepareResponse($response);

        foreach ($this->middleware as $middleware) {
            $instance = $this->make($middleware);

            if (method_exists($instance, 'terminate')) {
                $instance->terminate($this->make('request'), $response);
            }
        }
    }

    /**
     * Dispatch the incoming request.
     *
     * @param  SymfonyRequest|null  $request
     * @return Response
     */
    public function dispatch($request = null)
    {
        if ($request) {
            $this->instance('Illuminate\Http\Request', $request);
            $this->ranServiceBinders['registerRequestBindings'] = true;

            $method = $request->getMethod();
            $pathInfo = $request->getPathInfo();
        } else {
            $method = $this->getMethod();
            $pathInfo = $this->getPathInfo();
        }

        try {
            return $this->sendThroughPipeline($this->middleware, function () use ($method, $pathInfo) {
                if (isset($this->routes[$method.$pathInfo])) {
                    return $this->handleFoundRoute([true, $this->routes[$method.$pathInfo]['action'], []]);
                }

                return $this->handleDispatcherResponse(
                    $this->createDispatcher()->dispatch($method, $pathInfo)
                );
            });
        } catch (Exception $e) {
            return $this->sendExceptionToHandler($e);
        } catch (Throwable $e) {
            return $this->sendExceptionToHandler($e);
        }
    }

    /**
     * Create a FastRoute dispatcher instance for the application.
     *
     * @return Dispatcher
     */
    protected function createDispatcher()
    {
        return $this->dispatcher ?: \FastRoute\simpleDispatcher(function ($r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['method'], $route['uri'], $route['action']);
            }
        });
    }

    /**
     * Set the FastRoute dispatcher instance.
     *
     * @param  \FastRoute\Dispatcher  $dispatcher
     * @return void
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handle the response from the FastRoute dispatcher.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function handleDispatcherResponse($routeInfo)
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new NotFoundHttpException;

            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedHttpException($routeInfo[1]);

            case Dispatcher::FOUND:
                return $this->handleFoundRoute($routeInfo);
        }
    }

    /**
     * Handle a route found by the dispatcher.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function handleFoundRoute($routeInfo)
    {
        $this->currentRoute = $routeInfo;

        $action = $routeInfo[1];

        // Pipe through route middleware...
        if (isset($action['middleware'])) {
            $middleware = $this->gatherMiddlewareClassNames($action['middleware']);

            return $this->prepareResponse($this->sendThroughPipeline($middleware, function () use ($routeInfo) {
                return $this->callActionOnArrayBasedRoute($routeInfo);
            }));
        }

        return $this->prepareResponse(
            $this->callActionOnArrayBasedRoute($routeInfo)
        );
    }

    /**
     * Call the Closure on the array based route.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function callActionOnArrayBasedRoute($routeInfo)
    {
        $action = $routeInfo[1];

        foreach ($action as $value) {
            if ($value instanceof Closure) {
                $closure = $value;
                break;
            }
        }

        try {
            return $this->prepareResponse($this->call($closure, $routeInfo[2]));
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Gather the full class names for the middleware short-cut string.
     *
     * @param  string  $middleware
     * @return array
     */
    protected function gatherMiddlewareClassNames($middleware)
    {
        $middleware = is_string($middleware) ? explode('|', $middleware) : (array) $middleware;

        return array_map(function ($name) {
            list($name, $parameters) = array_pad(explode(':', $name, 2), 2, null);

            return array_get($this->routeMiddleware, $name, $name).($parameters ? ':'.$parameters : '');
        }, $middleware);
    }

    /**
     * Send the request through the pipeline with the given callback.
     *
     * @param  array  $middleware
     * @param  \Closure  $then
     * @return mixed
     */
    protected function sendThroughPipeline(array $middleware, Closure $then)
    {
        $shouldSkipMiddleware = $this->bound('middleware.disable') &&
                                        $this->make('middleware.disable') === true;

        if (count($middleware) > 0 && ! $shouldSkipMiddleware) {
            return (new Pipeline($this))
                ->send($this->make('request'))
                ->through($middleware)
                ->then($then);
        }

        return $then();
    }

    /**
     * Prepare the response for sending.
     *
     * @param  mixed  $response
     * @return Response
     */
    public function prepareResponse($response)
    {
        if (! $response instanceof SymfonyResponse) {
            $response = new Response($response);
        } elseif ($response instanceof BinaryFileResponse) {
            $response = $response->prepare(Request::capture());
        }

        return $response;
    }

    /**
     * Get the current HTTP request method.
     *
     * @return string
     */
    protected function getMethod()
    {
        if (isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        } else {
            return $_SERVER['REQUEST_METHOD'];
        }
    }

    /**
     * Get the current HTTP path info.
     *
     * @return string
     */
    protected function getPathInfo()
    {
        $query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

        return '/'.trim(str_replace('?'.$query, '', $_SERVER['REQUEST_URI']), '/');
    }
}
