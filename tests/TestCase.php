<?php
namespace Tests;
abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        if ($app['config']->get('database.default') !== 'mysql' ||
            ! str_ends_with((string) $app['config']->get('database.connections.mysql.database'), '_testing')) {
            throw new \RuntimeException('Tests require a dedicated MySQL database ending in _testing.');
        }
        return $app;
    }
}
