<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route registration
    |--------------------------------------------------------------------------
    |
    | When true (the default), the package automatically registers its own
    | routes from routes/web.php, wrapped in the group settings below.
    |
    | Set this to false when installing inside an app that already defines the
    | same form routes (e.g. the GoldenHospital host it was extracted from), to
    | avoid duplicate route-name collisions.
    |
    */

    'register_routes' => true,

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

    'middleware' => ['web', 'auth', 'AuthGates', 'setLocale'],

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
