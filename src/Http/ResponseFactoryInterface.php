<?php

namespace Laravel\Lumen\Http;

interface ResponseFactoryInterface
{
    /**
     * Return a new response from the application.
     *
     * @param  string $content
     * @param  int $status
     * @param  array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function make($content = '', $status = 200, array $headers = []);

    /**
     * Return a new JSON response from the application.
     *
     * @param  string|array $data
     * @param  int $status
     * @param  array $headers
     * @param  int $options
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function json($data = [], $status = 200, array $headers = [], $options = 0);

    /**
     * Create a new file download response.
     *
     * @param  \SplFileInfo|string $file
     * @param  string $name
     * @param  array $headers
     * @param  null|string $disposition
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($file, $name = null, array $headers = [], $disposition = 'attachment');
}
