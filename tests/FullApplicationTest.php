<?php

use Mockery as m;
use Illuminate\Http\Request;
use Laravel\Lumen\Application;

class FullApplicationTest extends PHPUnit_Framework_TestCase
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

    public function testAddRouteMultipleMethodRequest()
    {
        $app = new Application;

        $app->addRoute(['GET', 'POST'], '/', function () {
            return response('Hello World');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());

        $response = $app->handle(Request::create('/', 'POST'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());
    }

    public function testRequestWithoutSymfonyClass()
    {
        $app = new Application;

        $app->get('/', function () {
            return response('Hello World');
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $response = $app->dispatch();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());

        unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    }

    public function testRequestWithoutSymfonyClassTrailingSlash()
    {
        $app = new Application;

        $app->get('/foo', function () {
            return response('Hello World');
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/';

        $response = $app->dispatch();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());

        unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    }

    public function testRequestWithParameters()
    {
        $app = new Application;

        $app->get('/foo/{bar}/{baz}', function ($bar, $baz) {
            return response($bar.$baz);
        });

        $response = $app->handle(Request::create('/foo/1/2', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('12', $response->getContent());
    }

    public function testCallbackRouteWithDefaultParameter()
    {
        $app = new Application;
        $app->get('/foo-bar/{baz}', function ($baz = 'default-value') {
            return response($baz);
        });

        $response = $app->handle(Request::create('/foo-bar/something', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('something', $response->getContent());
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

        $app->routeMiddleware(['foo' => 'LumenTestMiddleware', 'passing' => 'LumenTestPlainMiddleware']);

        $app->get('/', function () {
            return response('Hello World');
        });

        $app->get('/foo', ['middleware' => 'foo', function () {
            return response('Hello World');
        }]);

        $app->get('/bar', ['middleware' => ['foo'], function () {
            return response('Hello World');
        }]);

        $app->get('/fooBar', ['middleware' => 'passing|foo', function () {
            return response('Hello World');
        }]);

        $response = $app->handle(Request::create('/', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());

        $response = $app->handle(Request::create('/foo', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Middleware', $response->getContent());

        $response = $app->handle(Request::create('/bar', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Middleware', $response->getContent());

        $response = $app->handle(Request::create('/fooBar', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Middleware', $response->getContent());
    }

    public function testGlobalMiddlewareParameters()
    {
        $app = new Application;

        $app->middleware(['LumenTestParameterizedMiddleware:foo,bar']);

        $app->get('/', function () {
            return response('Hello World');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Middleware - foo - bar', $response->getContent());
    }

    public function testRouteMiddlewareParameters()
    {
        $app = new Application;

        $app->routeMiddleware(['foo' => 'LumenTestParameterizedMiddleware', 'passing' => 'LumenTestPlainMiddleware']);

        $app->get('/', ['middleware' => 'passing|foo:bar,boom', function () {
            return response('Hello World');
        }]);

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Middleware - bar - boom', $response->getContent());
    }

    public function testWithMiddlewareDisabled()
    {
        $app = new Application;

        $app->middleware(['LumenTestMiddleware']);
        $app->instance('middleware.disable', true);

        $app->get('/', function () {
            return response('Hello World');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());
    }

    public function testTerminableGlobalMiddleware()
    {
        $app = new Application;

        $app->middleware(['LumenTestTerminateMiddleware']);

        $app->get('/', function () {
            return response('Hello World');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('TERMINATED', $response->getContent());
    }

    public function testTerminateWithMiddlewareDisabled()
    {
        $app = new Application;

        $app->middleware(['LumenTestTerminateMiddleware']);
        $app->instance('middleware.disable', true);

        $app->get('/', function () {
            return response('Hello World');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());
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

    public function testUncaughtExceptionResponse()
    {
        $app = new Application;
        $app->instance('Illuminate\Contracts\Debug\ExceptionHandler', $mock = m::mock('Laravel\Lumen\Exceptions\Handler[report]'));
        $mock->shouldIgnoreMissing();

        $app->get('/', function () {
            throw new \RuntimeException('app exception');
        });

        $response = $app->handle(Request::create('/', 'GET'));
        $this->assertInstanceOf('Illuminate\Http\Response', $response);
    }

    public function testGeneratingUrls()
    {
        $app = new Application;
        $app->instance('request', Request::create('http://lumen.laravel.com', 'GET'));
        unset($app->availableBindings['request']);

        $app->get('/foo-bar', ['as' => 'foo', function () {
            //
        }]);

        $app->get('/foo-bar/{baz}/{boom}', ['as' => 'bar', function () {
            //
        }]);

        $this->assertEquals('http://lumen.laravel.com/something', url('something'));
        $this->assertEquals('http://lumen.laravel.com/foo-bar', route('foo'));
        $this->assertEquals('http://lumen.laravel.com/foo-bar/1/2', route('bar', ['baz' => 1, 'boom' => 2]));
        $this->assertEquals('http://lumen.laravel.com/foo-bar?baz=1&boom=2', route('foo', ['baz' => 1, 'boom' => 2]));
    }

    public function testGeneratingUrlsForRegexParameters()
    {
        $app = new Application;
        $app->instance('request', Request::create('http://lumen.laravel.com', 'GET'));
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

        $app->get('/foo-bar/{baz:[0-9]{2,5}}', ['as' => 'boom', function () {
            //
        }]);

        $this->assertEquals('http://lumen.laravel.com/something', url('something'));
        $this->assertEquals('http://lumen.laravel.com/foo-bar', route('foo'));
        $this->assertEquals('http://lumen.laravel.com/foo-bar/1/2', route('bar', ['baz' => 1, 'boom' => 2]));
        $this->assertEquals('http://lumen.laravel.com/foo-bar/1/2', route('baz', ['baz' => 1, 'boom' => 2]));
        $this->assertEquals('http://lumen.laravel.com/foo-bar/{baz:[0-9]+}/{boom:[0-9]+}?ba=1&bo=2', route('baz', ['ba' => 1, 'bo' => 2]));
        $this->assertEquals('http://lumen.laravel.com/foo-bar/5', route('boom', ['baz' => 5]));
    }

    public function testRegisterServiceProvider()
    {
        $app = new Application;
        $provider = new LumenTestServiceProvider($app);
        $app->register($provider);
    }

    public function testUsingCustomDispatcher()
    {
        $routes = new FastRoute\RouteCollector(new FastRoute\RouteParser\Std, new FastRoute\DataGenerator\GroupCountBased);

        $routes->addRoute('GET', '/', [function () {
            return response('Hello World');
        }]);

        $app = new Application;

        $app->setDispatcher(new FastRoute\Dispatcher\GroupCountBased($routes->getData()));

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());
    }

    public function testMiddlewareReceiveResponsesEvenWhenStringReturned()
    {
        unset($_SERVER['__middleware.response']);

        $app = new Application;

        $app->routeMiddleware(['foo' => 'LumenTestPlainMiddleware']);

        $app->get('/', ['middleware' => 'foo', function () {
            return 'Hello World';
        }]);

        $response = $app->handle(Request::create('/', 'GET'));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello World', $response->getContent());
        $this->assertEquals(true, $_SERVER['__middleware.response']);
    }

    public function testBasicControllerDispatching()
    {
        $app = new Application;

        $app->get('/show/{id}', 'LumenTestController@show');

        $response = $app->handle(Request::create('/show/25', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('25', $response->getContent());
    }

    public function testBasicControllerDispatchingWithGroup()
    {
        $app = new Application;
        $app->routeMiddleware(['test' => LumenTestMiddleware::class]);

        $app->group(['middleware' => 'test'], function ($app) {
            $app->get('/show/{id}', 'LumenTestController@show');
        });

        $response = $app->handle(Request::create('/show/25', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Middleware', $response->getContent());
    }

    public function testBasicControllerDispatchingWithGroupSuffix()
    {
        $app = new Application;
        $app->routeMiddleware(['test' => LumenTestMiddleware::class]);

        $app->group(['suffix' => '.{format:json|xml}'], function ($app) {
            $app->get('/show/{id}', 'LumenTestController@show');
        });

        $response = $app->handle(Request::create('/show/25.xml', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('25', $response->getContent());
    }

    public function testBasicControllerDispatchingWithGroupAndSuffixWithPath()
    {
        $app = new Application;
        $app->routeMiddleware(['test' => LumenTestMiddleware::class]);

        $app->group(['suffix' => '/{format:json|xml}'], function ($app) {
            $app->get('/show/{id}', 'LumenTestController@show');
        });

        $response = $app->handle(Request::create('/show/test/json', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('test', $response->getContent());
    }

    public function testBasicControllerDispatchingWithMiddlewareIntercept()
    {
        $app = new Application;
        $app->routeMiddleware(['test' => LumenTestMiddleware::class]);
        $app->get('/show/{id}', 'LumenTestControllerWithMiddleware@show');

        $response = $app->handle(Request::create('/show/25', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Middleware', $response->getContent());
    }

    public function testBasicInvokableActionDispatching()
    {
        $app = new Application;

        $app->get('/action/{id}', 'LumenTestAction');

        $response = $app->handle(Request::create('/action/199', 'GET'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('199', $response->getContent());
    }

    public function testEnvironmentDetection()
    {
        $app = new Application;

        $this->assertEquals('production', $app->environment());
        $this->assertTrue($app->environment('production'));
        $this->assertTrue($app->environment(['production']));
    }

    public function testNamespaceDetection()
    {
        $app = new Application;
        $this->setExpectedException('RuntimeException');
        $app->getNamespace();
    }

    public function testRunningUnitTestsDetection()
    {
        $app = new Application;

        $this->assertEquals(false, $app->runningUnitTests());
    }

    public function testValidationHelpers()
    {
        $app = new Application;

        $app->get('/', function (Illuminate\Http\Request $request) {
            $this->validate($request, ['name' => 'required']);
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testRedirectResponse()
    {
        $app = new Application;

        $app->get('/', function (Illuminate\Http\Request $request) {
            return redirect('home');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRedirectToNamedRoute()
    {
        $app = new Application;

        $app->get('login', ['as' => 'login', function (Illuminate\Http\Request $request) {
            return 'login';
        }]);

        $app->get('/', function (Illuminate\Http\Request $request) {
            return redirect()->route('login');
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRequestUser()
    {
        $app = new Application();

        $app['auth']->viaRequest('api', function ($request) {
            return new \Illuminate\Auth\GenericUser(['id' => 1234]);
        });

        $app->get('/', function (Illuminate\Http\Request $request) {
            return $request->user()->getAuthIdentifier();
        });

        $response = $app->handle(Request::create('/', 'GET'));

        $this->assertSame('1234', $response->getContent());
    }

    public function testCanResolveValidationFactoryFromContract()
    {
        $app = new Application();

        $validator = $app['Illuminate\Contracts\Validation\Factory'];

        $this->assertInstanceOf('Illuminate\Contracts\Validation\Factory', $validator);
    }

    public function testCanMergeUserProvidedFacadesWithDefaultOnes()
    {
        $app = new Application();

        $aliases = [
            UserFacade::class => 'Foo',
        ];

        $app->withFacades(true, $aliases);

        $this->assertTrue(class_exists('Foo'));
    }
}

class LumenTestService
{
}

class LumenTestServiceProvider extends Illuminate\Support\ServiceProvider
{
    public function register()
    {
    }
}

class LumenTestController
{
    public function __construct(LumenTestService $service)
    {
        //
    }

    public function show($id)
    {
        return $id;
    }
}

class LumenTestControllerWithMiddleware extends Laravel\Lumen\Routing\Controller
{
    public function __construct(LumenTestService $service)
    {
        $this->middleware('test');
    }

    public function show($id)
    {
        return $id;
    }
}

class LumenTestMiddleware
{
    public function handle($request, $next)
    {
        return response('Middleware');
    }
}

class LumenTestPlainMiddleware
{
    public function handle($request, $next)
    {
        $response = $next($request);
        $_SERVER['__middleware.response'] = $response instanceof Illuminate\Http\Response;

        return $response;
    }
}

class LumenTestParameterizedMiddleware
{
    public function handle($request, $next, $parameter1, $parameter2)
    {
        return response("Middleware - $parameter1 - $parameter2");
    }
}

class LumenTestAction
{
    public function __invoke($id)
    {
        return $id;
    }
}

class UserFacade
{
}

class LumenTestTerminateMiddleware
{
    public function handle($request, $next)
    {
        return $next($request);
    }

    public function terminate($request, Illuminate\Http\Response $response)
    {
        $response->setContent('TERMINATED');
    }
}
