<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Vulnerar\Agent\AgentServiceProvider;


abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AgentServiceProvider::class,
        ];
    }
}
