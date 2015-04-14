<?php
namespace Laravel\Lumen\Tests;

use Mockery as m;
use Illuminate\Http\Request;
use Laravel\Lumen\Application;
use PHPUnit_Framework_TestCase;

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

        $app->middleware(['Laravel\Lumen\Tests\LumenTestMiddleware']);

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

        $app->routeMiddleware(['foo' => 'Laravel\Lumen\Tests\LumenTestMiddleware']);

        $app->get('/', function () {
            return response('Hello World');
        });

        $app->get('/foo', ['middleware' => 'foo', function () {
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

        $app->routeMiddleware(['foo' => 'Laravel\Lumen\Tests\LumenTestMiddleware']);

        $app->group(['middleware' => 'foo'], function ($app) {
            $app->get('/', function () {
                return response('Hello World');
            });
        });

        $app->get('/foo', function () {
            return response('Hello World');
        });

        $response = $app->handle(Request::create('/', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Middleware', $response->getContent());

        $response = $app->handle(Request::create('/foo', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());
    }


    public function testNotFoundResponse()
    {
        $app = new Application;
        $app->instance(
            'Illuminate\Contracts\Debug\ExceptionHandler',
            $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]')
        );
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
        $app->instance(
            'Illuminate\Contracts\Debug\ExceptionHandler',
            $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]')
        );
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

        $app->get('/', 'Laravel\Lumen\Tests\LumenTestController@action');

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Laravel\Lumen\Tests\LumenTestController', $response->getContent());
    }


    public function testNamespacedControllerResponse()
    {
        $app = new Application;

        $app->group(['namespace' => ''], function ($app) {
            $app->get('/', 'Laravel\Lumen\Tests\TestController@action');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Laravel\Lumen\Tests\TestController', $response->getContent());
    }


    public function testGeneratingUrls()
    {
        $app = new Application;
        $app->instance('request', Request::create('http://lumen.com', 'GET'));
        unset($app->availableBindings['request']);

        $app->get('/foo-bar', ['as' => 'foo', function () {
            //
        }]);

        $app->get('/foo-bar/{baz}/{boom}', ['as' => 'bar', function () {
            //
        }]);

        $this->assertEquals('http://lumen.com/something', url('something'));
        $this->assertEquals('http://lumen.com/foo-bar', route('foo'));
        $this->assertEquals('http://lumen.com/foo-bar/1/2', route('bar', ['baz' => 1, 'boom' => 2]));
    }


    public function testGeneratingUrlsForRegexParameters()
    {
        $app = new Application;
        $app->instance('request', Request::create('http://lumen.com', 'GET'));
        unset($app->availableBindings['request']);

        $app->get('/foo-bar', ['as' => 'foo', function () {
            //
        }]);

        $app->get('/foo-bar/{baz:[0-9]+}/{boom}', ['as' => 'bar', function () {
            //
        }]);

        $app->get('/foo-bar/{baz:[0-9]+}/{boom:[0-9]+}', ['as' => 'baz', function () {
            //
        }]);

        $this->assertEquals('http://lumen.com/something', url('something'));
        $this->assertEquals('http://lumen.com/foo-bar', route('foo'));
        $this->assertEquals('http://lumen.com/foo-bar/1/2', route('bar', ['baz' => 1, 'boom' => 2]));
        $this->assertEquals('http://lumen.com/foo-bar/1/2', route('baz', ['baz' => 1, 'boom' => 2]));
    }
}
