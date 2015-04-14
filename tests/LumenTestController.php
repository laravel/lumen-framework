<?php
namespace Laravel\Lumen\Tests;

class LumenTestController
{
    public $service;
    public function __construct(LumenTestService $service)
    {
        $this->service = $service;
    }
    public function action()
    {
        return response(__CLASS__);
    }
}
