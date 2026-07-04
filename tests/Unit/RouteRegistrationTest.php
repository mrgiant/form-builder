<?php

namespace Mrgiant\FormBuilder\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Mrgiant\FormBuilder\Tests\TestCase;

class RouteRegistrationTest extends TestCase
{
    public function test_routes_are_registered_automatically_by_default(): void
    {
        $this->assertTrue(config('form-builder.register_routes'));
        $this->assertTrue(Route::has('admin.forms.index'), 'admin.forms.index route should be auto-registered');
        $this->assertTrue(Route::has('admin.approvals.inbox'));
    }
}
