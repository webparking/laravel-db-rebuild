<?php

namespace Webparking\DbRebuild\Tests;

use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Webparking\DbRebuild\ServiceProvider;

class TestCase extends OrchestraTestCase
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
            ConsoleServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->useDatabasePath(__DIR__ . '/database');
    }
}
