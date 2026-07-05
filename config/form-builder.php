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
    | Public (front-end) form routes
    |--------------------------------------------------------------------------
    |
    | The no-auth routes end users hit to view and fill a form by its slug
    | (GET /forms/{slug}). Registered at the application root — no 'admin'
    | prefix, no 'auth'. If your app prefixes URLs by locale (e.g. /en/forms/..)
    | via a route GROUP, disable this and point one route in your own locale
    | group at PublicFormController::view_form instead.
    |
    */

    'register_public_routes' => true,

    'public_route_prefix' => '',

    'public_middleware' => ['web'],

    /*
    | Vite entry points loaded on the standard (non custom-HTML) public form
    | page, so the <forms-questions-answers> component can mount. Match your
    | host's build. Set to [] if you inject the bundle another way.
    */

    'public_assets' => ['resources/js/app.js'],

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
