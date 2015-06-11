<?php

use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\TestCase;
use Illuminate\Support\Facades\Auth;

class TestingTest extends TestCase
{

    public function createApplication()
    {
        return new Application;
    }

    /**
     * The two tests testFirstMethodUsingFacades and testSecondMethodUsingFacades works in pair to show a problem
     * with missing call to "Facade::clearResolvedInstances()" when using Lumen Testing TestCase.
     * It recreates app instance on each test, but also needs to clear the facade instances created with the
     * previous app instance.
     */
    public function testFirstMethodUsingFacades()
    {
        $this->app->withFacades();
        Auth::user();
    }
    public function testSecondMethodUsingFacades()
    {
        $this->app->withFacades();
        Auth::user();
    }
}



