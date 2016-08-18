<?php

namespace Laravel\Lumen\Http;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResponseFactory
{
    /**
     * Return a new response from the application.
     *
     * @param  string  $content
     * @param  int     $status
     * @param  array   $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function make($content = '', $status = 200, array $headers = [])
    {
        $response = new Response($content, $status, $headers);
        $this->prepare($response);

        return $response;
    }

    /**
     * Return a new JSON response from the application.
     *
     * @param  string|array  $data
     * @param  int    $status
     * @param  array  $headers
     * @param  int    $options
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $response = new JsonResponse($data, $status, $headers, $options);
        $this->prepare($response);

        return $response;
    }

    /**
     * Create a new file download response.
     *
     * @param  \SplFileInfo|string  $file
     * @param  string  $name
     * @param  array   $headers
     * @param  null|string  $disposition
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($file, $name = null, array $headers = [], $disposition = 'attachment')
    {
        $response = new BinaryFileResponse($file, 200, $headers, true, $disposition);
        $this->prepare($response);

        if (! is_null($name)) {
            return $response->setContentDisposition($disposition, $name, str_replace('%', '', Str::ascii($name)));
        }

        return $response;
    }

    /**
     * Fix up the default Response settings.
     *
     * This is a light version of Symfony's prepare method.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepare(SymfonyResponse $response)
    {
        $response->setProtocolVersion('1.1');

        return $response;
    }
}
