<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EnvironmentTest extends TestCase
{
    public function test_environment_is_testing()
    {
        // Assert that the environment is 'testing'
        $this->assertEquals('testing', env('APP_ENV'));
    }

    public function test_database_is_testing_database()
    {
        // Assert that the database name matches the one in .env.testing
        $this->assertEquals('kenibackend_testing', env('DB_DATABASE'));
    }
}
