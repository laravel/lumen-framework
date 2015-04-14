<?php
namespace Laravel\Lumen\Tests;

class LumenTestMiddleware
{
    public function handle($request, $next)
    {
        return response('Middleware');
    }
}
