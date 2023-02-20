<?php

use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Log\LogManager;
use Laravel\Lumen\Concerns\RegistersExceptionHandlers;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class HandleExceptionsTest extends TestCase
{
    use RegistersExceptionHandlers;

    protected $container;

    protected $config;

    protected function setUp(): void
    {
        $this->container = new Container;

        $this->config = new Config();

        $this->container->singleton('config', function () {
            return $this->config;
        });
    }

    protected function tearDown(): void
    {
        $this->container::setInstance(null);

        m::close();
    }

    public function testPhpDeprecations()
    {
        $logger = m::mock(LogManager::class);
        $this->container->instance('log', $logger);
        $logger->shouldReceive('channel')->with('deprecations')->andReturnSelf();
        $logger->shouldReceive('warning')->with(sprintf('%s in %s on line %s',
            'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
            '/home/user/laravel/routes/web.php',
            17
        ));

        $this->handleError(
            E_DEPRECATED,
            'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
            '/home/user/laravel/routes/web.php',
            17
        );
    }

    public function testUserDeprecations()
    {
        $logger = m::mock(LogManager::class);
        $this->container->instance('log', $logger);
        $logger->shouldReceive('channel')->with('deprecations')->andReturnSelf();
        $logger->shouldReceive('warning')->with(sprintf('%s in %s on line %s',
            'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
            '/home/user/laravel/routes/web.php',
            17
        ));

        $this->handleError(
            E_USER_DEPRECATED,
            'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
            '/home/user/laravel/routes/web.php',
            17
        );
    }

    public function testErrors()
    {
        $logger = m::mock(LogManager::class);
        $this->container->instance('log', $logger);
        $logger->shouldNotReceive('channel');
        $logger->shouldNotReceive('warning');

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Something went wrong');

        $this->handleError(
            E_ERROR,
            'Something went wrong',
            '/home/user/laravel/src/Providers/AppServiceProvider.php',
            17
        );
    }

    public function testEnsuresDeprecationsDriver()
    {
        $logger = m::mock(LogManager::class);
        $this->container->instance('log', $logger);
        $logger->shouldReceive('channel')->andReturnSelf();
        $logger->shouldReceive('warning');

        $this->config->set('logging.channels.stack', [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ]);
        $this->config->set('logging.deprecations', 'stack');

        $this->handleError(
            E_USER_DEPRECATED,
            'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
            '/home/user/laravel/routes/web.php',
            17
        );

        $this->assertEquals(
            [
                'driver' => 'stack',
                'channels' => ['single'],
                'ignore_exceptions' => false,
            ],
            $this->config->get('logging.channels.deprecations')
        );
    }

    public function testEnsuresNullDeprecationsDriver()
    {
        $logger = m::mock(LogManager::class);
        $this->container->instance('log', $logger);
        $logger->shouldReceive('channel')->andReturnSelf();
        $logger->shouldReceive('warning');

        $this->config->set('logging.channels.null', [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ]);

        $this->handleError(
            E_USER_DEPRECATED,
            'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
            '/home/user/laravel/routes/web.php',
            17
        );

        $this->assertEquals(
            NullHandler::class,
            $this->config->get('logging.channels.deprecations.handler')
        );
    }

    public function testNoDeprecationsDriverIfNoDeprecationsHereSend()
    {
        $this->assertEquals(null, $this->config->get('logging.deprecations'));
        $this->assertEquals(null, $this->config->get('logging.channels.deprecations'));
    }

    public function testIgnoreDeprecationIfLoggerUnresolvable()
    {
        $this->handleError(
            E_DEPRECATED,
            'str_contains(): Passing null to parameter #2 ($needle) of type string is deprecated',
            '/home/user/laravel/routes/web.php',
            17
        );
    }

    protected function make($abstract, array $parameters = [])
    {
        return $this->container->make($abstract, $parameters);
    }
}
