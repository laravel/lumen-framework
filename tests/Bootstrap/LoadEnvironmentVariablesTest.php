<?php

use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class LoadEnvironmentVariablesTest extends TestCase
{
    public function testShouldLoadDefaultEnvFile(): void
    {
        (new LoadEnvironmentVariables(
            __DIR__.'/Dotenv'
        ))->bootstrap();

        $this->assertEquals('foo', env('FOO'), 'Check that .env is loaded');
        $this->assertNull(env('BAR'), 'Check that .env.bar is not loaded');
    }

    public function testShouldLoadEnvFileByName(): void
    {
        (new LoadEnvironmentVariables(
            __DIR__.'/Dotenv',
            '.env.bar'
        ))->bootstrap();

        $this->assertNull(env('FOO'), 'Check that .env is not loaded');
        $this->assertEquals('bar', env('BAR'), 'Check that .env.bar is loaded');
    }

    public function testShouldLoadOnlyFirstEnvFileWhenShortCircuitIsEnabled(): void
    {
        (new LoadEnvironmentVariables(
            __DIR__.'/Dotenv',
            ['.env', '.env.bar']
        ))->bootstrap();

        $this->assertEquals('foo', env('FOO'), 'Check that .env is loaded');
        $this->assertNull(env('BAR'), 'Check that .env.bar is not loaded');
    }

    public function testShouldLoadAllEnvFilesWhenShortCircuitIsDisabled(): void
    {
        (new LoadEnvironmentVariables(
            __DIR__.'/Dotenv',
            ['.env', '.env.bar'],
            false
        ))->bootstrap();

        $this->assertEquals('foo', env('FOO'), 'Check that .env is loaded');
        $this->assertEquals('bar', env('BAR'), 'Check that .env.bar is loaded');
    }
}
