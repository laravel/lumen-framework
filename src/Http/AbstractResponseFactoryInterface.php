<?php

namespace Laravel\Lumen\Http;

interface AbstractResponseFactoryInterface
{
    /**
     * @param  string $type
     * @param  ResponseFactoryInterface $factory
     * @return void
     */
    public function addFactory($type, ResponseFactoryInterface $factory);

    /**
     * @param  string $type
     * @return ResponseFactoryInterface
     */
    public function make($type);
}
