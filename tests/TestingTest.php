<?php

use Mockery as m;
use Illuminate\Http\Request;
use Laravel\Lumen\Application;

class TestingTest extends \Laravel\Lumen\Testing\TestCase
{
    public function createApplication()
    {
        return new Application;
    }

    //
    // These two tests works in pair to show a problem with missing call to "Facade::clearResolvedInstances()"
    // when using Lumen Testing TestCase. It recreates app instance on each test, but also needs to clear the
    // facade instances created with the previous app instance.
    //
    public function testFirstMethodUsingFacades()
    {
        $this->app->withFacades();
        Illuminate\Support\Facades\Auth::user();
    }
    public function testSecondMethodUsingFacades()
    {
        $this->app->withFacades();
        Illuminate\Support\Facades\Auth::user();

    }
}



