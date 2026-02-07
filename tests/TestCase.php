<?php

namespace Tests;

use Illuminate\Support\Facades\Auth;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Vulnerar\Agent\AgentServiceProvider;


abstract class TestCase extends BaseTestCase
{
    use WithWorkbench;

    /**
     * @todo Replace middleware with Auth::basic()
     */
    protected function defineRoutes($router): void
    {
        $router->get('/basic', function () {
            return Auth::user();
        })->middleware('auth.basic');
    }

    protected function getPackageProviders($app): array
    {
        return [
            AgentServiceProvider::class,
        ];
    }
}
