<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route registration
    |--------------------------------------------------------------------------
    |
    | When false (the default), the package does NOT register its own routes.
    | This is intentional so that installing the package inside an app that
    | already defines the form routes (e.g. the GoldenHospital host it was
    | extracted from) does not cause duplicate route-name collisions.
    |
    | During a full cutover — once the host's own form routes are removed —
    | set this to true to have the package own the routes in routes/web.php.
    |
    */

    'register_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Route group settings
    |--------------------------------------------------------------------------
    |
    | Applied to the package route file when 'register_routes' is enabled.
    |
    */

    'route_prefix' => 'admin',

    'route_name_prefix' => 'admin.',

    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | User / authorization model
    |--------------------------------------------------------------------------
    |
    | The application user model that approval actions belong to.
    |
    */

    'user_model' => \App\Models\User::class,

];
