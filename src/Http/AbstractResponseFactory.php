<?php

namespace Laravel\Lumen\Http;

class AbstractResponseFactory implements AbstractResponseFactoryInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    private $factories;

    /**
     * Get an instance of the AbstractFactory.
     *
     * @return void
     */
    public function __construct()
    {
        $this->factories['default'] = new ResponseFactory;
    }

    /**
     * @param  string $type
     * @param  ResponseFactoryInterface $factory
     * @return void
     */
    public function addFactory($type, ResponseFactoryInterface $factory)
    {
        $this->factories[$type] = $factory;
    }

    /**
     * Makes a instance of a ResponseFactory.
     *
     * @param  string $type
     * @return ResponseFactoryInterface
     * @throws \Exception
     */
    public function make($type)
    {
        $type = (null === $type ? 'default' : $type);

        if (! isset($this->factories[$type])) {
            throw new \RuntimeException('cant find ResponseFactory: '.$type);
        }

        return $this->factories['default'];
    }
}
