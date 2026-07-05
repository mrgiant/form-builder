<?php

// Mirrors a standard host's App\Http\Controllers\Controller (Laravel's default
// ships this with the traits) so the package controllers, which extend it and
// call $this->validate()/$this->authorize(), can be exercised in tests. The
// bare testbench skeleton doesn't provide this class.

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

if (! class_exists(\App\Http\Controllers\Controller::class, false)) {
    class Controller extends BaseController
    {
        use AuthorizesRequests, ValidatesRequests;
    }
}
