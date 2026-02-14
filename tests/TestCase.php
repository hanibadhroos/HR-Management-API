<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        // $this->withoutMiddleware(\Illuminate\Auth\Middleware\Authenticate::class);


        \Illuminate\Support\Facades\Gate::before(function () {
            return true;
        });
    }
}
