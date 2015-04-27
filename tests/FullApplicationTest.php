<?php

use Mockery as m;
use Illuminate\Http\Request;
use Laravel\Lumen\Application;

class ExampleTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }


    public function testBasicRequest()
    {
        $app = new Application;

        $app->get('/', function () {
            return response('Hello World');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());
    }


    public function testGlobalMiddleware()
    {
        $app = new Application;

        $app->middleware(['LumenTestMiddleware']);

        $app->get('/', function () {
            return response('Hello World');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Middleware', $response->getContent());
    }


    public function testRouteMiddleware()
    {
        $app = new Application;

        $app->routeMiddleware(['foo' => 'LumenTestMiddleware']);

        $app->get('/', function () {
            return response('Hello World');
        });

        $app->get('/foo', ['middleware' => 'foo', function() {
            return response('Hello World');
        }]);

        $response = $app->handle(Request::create('/', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());

        $response = $app->handle(Request::create('/foo', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Middleware', $response->getContent());
    }


    public function testGroupRouteMiddleware()
    {
        $app = new Application;

        $app->routeMiddleware(['foo' => 'LumenTestMiddleware']);

        $app->group(['middleware' => 'foo'], function($app) {
            $app->get('/', function () {
                return response('Hello World');
            });
        });

        $app->get('/foo', function() {
            return response('Hello World');
        });

        $response = $app->handle(Request::create('/', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Middleware', $response->getContent());

        $response = $app->handle(Request::create('/foo', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());
    }


    public function testGroupPrefixRoutes()
    {
        $app = new Application;

        $app->group(['prefix' => 'user'], function($app) {
            $app->get('/', function () {
                return response('User Index');
            });

            $app->get('profile', function () {
                return response('User Profile');
            });

            $app->get('/show', function () {
                return response('User Show');
            });
        });

        $response = $app->handle(Request::create('/user', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('User Index', $response->getContent());

        $response = $app->handle(Request::create('/user/profile', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('User Profile', $response->getContent());

        $response = $app->handle(Request::create('/user/show', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('User Show', $response->getContent());
    }


    public function testNotFoundResponse()
    {
        $app = new Application;
        $app->instance('Illuminate\Contracts\Debug\ExceptionHandler', $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]'));
        $mock->shouldIgnoreMissing();

        $app->get('/', function () {
            return response('Hello World');
        });

        $response = $app->handle(Request::create('/foo', 'GET'));

        $this->assertEquals(404, $response->getStatusCode());
    }


    public function testMethodNotAllowedResponse()
    {
        $app = new Application;
        $app->instance('Illuminate\Contracts\Debug\ExceptionHandler', $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]'));
        $mock->shouldIgnoreMissing();

        $app->post('/', function () {
            return response('Hello World');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(405, $response->getStatusCode());
    }


    public function testControllerResponse()
    {
        $app = new Application;

        $app->get('/', 'LumenTestController@action');

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('LumenTestController', $response->getContent());
    }


    public function testNamespacedControllerResponse()
    {
        $app = new Application;

        require_once __DIR__.'/fixtures/TestController.php';

        $app->group(['namespace' => 'Lumen\Tests'], function($app) {
            $app->get('/', 'TestController@action');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Lumen\Tests\TestController', $response->getContent());
    }


    public function testGeneratingUrls()
    {
        $app = new Application;
        $app->instance('request', Request::create('http://lumen.laravel.com', 'GET'));
        unset($app->availableBindings['request']);

        $app->get('/foo-bar', ['as' => 'foo', function() {
            //
        }]);

        $app->get('/foo-bar/{baz}/{boom}', ['as' => 'bar', function() {
            //
        }]);

        $this->assertEquals('http://lumen.laravel.com/something', url('something'));
        $this->assertEquals('http://lumen.laravel.com/foo-bar', route('foo'));
        $this->assertEquals('http://lumen.laravel.com/foo-bar/1/2', route('bar', ['baz' => 1, 'boom' => 2]));
    }


    public function testGeneratingUrlsForRegexParameters()
    {
        $app = new Application;
        $app->instance('request', Request::create('http://lumen.laravel.com', 'GET'));
        unset($app->availableBindings['request']);

        $app->get('/foo-bar', ['as' => 'foo', function() {
            //
        }]);

        $app->get('/foo-bar/{baz:[0-9]+}/{boom}', ['as' => 'bar', function() {
            //
        }]);

        $app->get('/foo-bar/{baz:[0-9]+}/{boom:[0-9]+}', ['as' => 'baz', function() {
            //
        }]);

        $this->assertEquals('http://lumen.laravel.com/something', url('something'));
        $this->assertEquals('http://lumen.laravel.com/foo-bar', route('foo'));
        $this->assertEquals('http://lumen.laravel.com/foo-bar/1/2', route('bar', ['baz' => 1, 'boom' => 2]));
        $this->assertEquals('http://lumen.laravel.com/foo-bar/1/2', route('baz', ['baz' => 1, 'boom' => 2]));
    }

    public function testRegisterServiceProvider()
    {
        $app = new Application;
        $provider = new LumenTestServiceProvider($app);
        $app->register($provider);
    }
}

class LumenTestService {}

class LumenTestServiceProvider extends Illuminate\Support\ServiceProvider
{
    public function register() {}
}

class LumenTestMiddleware {
    public function handle($request, $next) {
          return response('Middleware');
    }
}

class LumenTestController {
    public $service;
    public function __construct(LumenTestService $service) {
        $this->service = $service;
    }
    public function action() {
        return response(__CLASS__);
    }
}
