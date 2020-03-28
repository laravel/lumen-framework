<?php

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Application;
use Laravel\Lumen\Http\Request;
use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;
use PHPUnit\Framework\TestCase;

class MakesHttpRequestsTest extends TestCase
{
    use MakesHttpRequests;

    public function testReceiveJson()
    {
        $this->app = new Application;
        $this->app->router->get('/', function () {
            return new JsonResponse(['foo' => 'bar', 'hello' => 'world']);
        });

        $this->handle(Request::create('/', 'GET'));

        // Test response is json
        $this->receiveJson();

        // Test response contains fragment
        $this->receiveJson(['foo' => 'bar']);
    }
}
