<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Http\ResponseFactory;

class ResponseFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testMakeDefaultResponse()
    {
        $content = 'hello';
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->make($content);
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals($content, $response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testJsonDefaultResponse()
    {
        $content = ['hello' => 'world'];
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->json($content);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals('{"hello":"world"}', $response->getContent());
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

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(false, $response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        unlink($temp);
    }

    public function testJsonResponseFromArrayableInterface()
    {
        // mock one Arrayable object
        $content = $this->getMockBuilder('Illuminate\Contracts\Support\Arrayable')
            ->setMethods(['toArray'])
            ->getMock();
        $content->expects($this->once())
            ->method('toArray')
            ->willReturn(['hello' => 'world']);

        $responseFactory = new ResponseFactory();
        $response = $responseFactory->json($content);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals('{"hello":"world"}', $response->getContent());
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
