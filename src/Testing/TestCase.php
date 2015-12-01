<?php

namespace Laravel\Lumen\Testing;

use Mockery;
use PHPUnit_Framework_TestCase;
use Illuminate\Support\Facades\Facade;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    use Concerns\MakesHttpRequests;

    /**
     * The application instance.
     *
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    abstract public function createApplication();

    /**
     * Refresh the application instance.
     *
     * @return void
     */
    protected function refreshApplication()
    {
        putenv('APP_ENV=testing');

        $this->app = $this->createApplication();

        Facade::clearResolvedInstances();
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        if (! $this->app) {
            $this->refreshApplication();
        }
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    public function tearDown()
    {
        if (class_exists('Mockery')) {
            Mockery::close();
        }

        if ($this->app) {
            $this->app->flush();
            $this->app = null;
        }
    }

    /**
     * Assert that a given where condition exists in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @return $this
     */
    protected function seeInDatabase($table, array $data)
    {
        $count = $this->app->make('db')->table($table)->where($data)->count();

        $this->assertGreaterThan(0, $count, sprintf(
            'Unable to find row in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }

    /**
     * Assert that a given where condition does not exist in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @return $this
     */
    protected function missingFromDatabase($table, array $data)
    {
        return $this->notSeeInDatabase($table, $data);
    }

    /**
     * Assert that a given where condition does not exist in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @return $this
     */
    protected function notSeeInDatabase($table, array $data)
    {
        $count = $this->app->make('db')->table($table)->where($data)->count();

        $this->assertEquals(0, $count, sprintf(
            'Found unexpected records in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }
}
