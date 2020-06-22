<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Response;
use Laravel\Lumen\Http\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseFactoryTest extends TestCase
{
    public function testMakeDefaultResponse()
    {
        $content = 'hello';
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->make($content);
        $this->assertInstanceOf(SymfonyResponse::class, $response);
        $this->assertEquals($content, $response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testJsonDefaultResponse()
    {
        $content = ['hello' => 'world'];
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->json($content);

        $this->assertInstanceOf(SymfonyResponse::class, $response);
        $this->assertEquals('{"hello":"world"}', $response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testStreamDefaultResponse()
    {
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->stream(function () {
            echo 'hello';
        });

        $this->assertInstanceOf(SymfonyResponse::class, $response);
        $this->assertFalse($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testDownloadDefaultResponse()
    {
        $temp = tempnam(sys_get_temp_dir(), 'fixture');
        $fh = fopen($temp, 'w+');
        fwrite($fh, 'writing to tempfile');
        fclose($fh);

        $responseFactory = new ResponseFactory();
        $response = $responseFactory->download($temp);

        $this->assertInstanceOf(SymfonyResponse::class, $response);
        $this->assertFalse($response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        unlink($temp);
    }

    public function testJsonResponseFromArrayableInterface()
    {
        // mock one Arrayable object
        $content = $this->getMockBuilder(Arrayable::class)
            ->setMethods(['toArray'])
            ->getMock();
        $content->expects($this->once())
            ->method('toArray')
            ->willReturn(['hello' => 'world']);

        $responseFactory = new ResponseFactory();
        $response = $responseFactory->json($content);

        $this->assertInstanceOf(SymfonyResponse::class, $response);
        $this->assertEquals('{"hello":"world"}', $response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testStreamDeferredCallback()
    {
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->stream(function () {
            $this->fail();
        });

        $this->assertFalse($response->getContent());
    }
}
