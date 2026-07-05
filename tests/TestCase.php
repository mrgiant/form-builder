<?php

namespace Mrgiant\FormBuilder\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mrgiant\FormBuilder\FormBuilderServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        // The package controllers extend the host's base controller, which the
        // bare testbench skeleton doesn't ship. Alias it to the framework base
        // so controller-backed routes can be exercised in tests.
        if (! class_exists(\App\Http\Controllers\Controller::class)) {
            class_alias(\Illuminate\Routing\Controller::class, \App\Http\Controllers\Controller::class);
        }

        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            FormBuilderServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }

    /**
     * Provide the minimal `users` table the workflow schema references via
     * foreign keys. The package's own migrations are auto-registered by the
     * service provider and run by RefreshDatabase.
     */
    protected function defineDatabaseMigrations(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->timestamps();
            });
        }
    }
}
