<?php

use Illuminate\Support\Facades\Route;
use Mrgiant\FormBuilder\Http\Controllers\AnswerController;
use Mrgiant\FormBuilder\Http\Controllers\FormsController;
use Mrgiant\FormBuilder\Http\Controllers\PublicFormController;
use Mrgiant\FormBuilder\Http\Controllers\QuestionsController;

/*
|--------------------------------------------------------------------------
| Public (front-end) form routes
|--------------------------------------------------------------------------
|
| No-auth routes end users hit to view/fill a form by slug. Registered at
| the application root (no 'admin' prefix). Only loaded when
| config('form-builder.register_public_routes') is true.
|
| If your app prefixes URLs by locale via a route GROUP (e.g. /en/forms/..),
| disable this config flag and add one line to your own locale group:
|   Route::get('forms/{slug}', [PublicFormController::class, 'view_form']);
|
*/

Route::prefix(config('form-builder.public_route_prefix'))
    ->middleware(config('form-builder.public_middleware'))
    ->group(function () {
        Route::get('forms/{slug}/closedform', [PublicFormController::class, 'closedform'])->name('frontend.form.closedform');
        Route::get('forms/{slug}/NotStartForm', [PublicFormController::class, 'NotStartForm'])->name('frontend.form.NotStartForm');

        // Data endpoints the public form-fill component (and custom-HTML forms) call.
        // Bound by id — the front-end passes $form->id.
        Route::get('forms/{form}/info', [FormsController::class, 'showPublic'])->name('frontend.form.info');
        Route::get('forms/{form}/questions', [QuestionsController::class, 'index'])->name('frontend.form.questions');
        Route::post('forms/{form}/d', [AnswerController::class, 'store'])->name('frontend.form.submit');

        Route::get('forms/{slug}', [PublicFormController::class, 'view_form'])->name('frontend.form.view_form');
    });
