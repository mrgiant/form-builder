<?php

use Illuminate\Support\Facades\Route;
use Mrgiant\FormBuilder\Http\Controllers\AnswerController;
use Mrgiant\FormBuilder\Http\Controllers\FormsController;
use Mrgiant\FormBuilder\Http\Controllers\QuestionsController;
use Mrgiant\FormBuilder\Http\Controllers\ResponsesController;
use Mrgiant\FormBuilder\Http\Controllers\WorkflowController;

/*
|--------------------------------------------------------------------------
| Form Builder routes
|--------------------------------------------------------------------------
|
| Only loaded when config('form-builder.register_routes') is true. The group
| prefix, name-prefix and middleware are driven by config so the host app can
| mount the module wherever it likes. These mirror the route names the Vue
| components and Blade views expect (admin.forms.*, admin.questions.*, etc.).
|
*/

Route::prefix(config('form-builder.route_prefix'))
    ->name(config('form-builder.route_name_prefix'))
    ->middleware(config('form-builder.middleware'))
    ->group(function () {

        // Forms
        Route::controller(FormsController::class)->prefix('forms')->name('forms.')->group(function () {
            Route::get('getFormsList', 'getFormsList')->name('getFormsList');
            Route::get('response', 'response')->name('response');
            Route::get('downloadAttachment/{attachment}', 'downloadAttachment')->name('downloadAttachment');
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('{form}/design', 'design')->name('design');
            Route::put('{form}/design', 'saveDesign')->name('design.save');
            Route::delete('{form}/design', 'deleteDesign')->name('design.delete');
            Route::put('{form}', 'update')->name('update');
            Route::delete('{form}', 'destroy')->name('destroy');
        });

        // Questions
        Route::put('questions/QuestionsUpdateOrder', [QuestionsController::class, 'QuestionsUpdateOrder'])->name('questions.QuestionsUpdateOrder');
        Route::controller(QuestionsController::class)->group(function () {
            Route::prefix('questions')->name('questions.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('{question}', 'update')->name('update');
                Route::delete('{question}', 'destroy')->name('destroy');
            });

            Route::prefix('forms/{form}/questions')->name('forms.questions.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::post('import', 'import')->name('import');
                Route::get('{question}/edit', 'edit')->name('edit');
                Route::put('{question}', 'update')->name('update');
                Route::delete('{question}', 'destroy')->name('destroy');
            });
        });

        // Answers
        Route::controller(AnswerController::class)->group(function () {
            Route::get('forms/{form}/view', 'response')->name('forms.response.view');
            Route::post('forms/{form}/d', 'store')->name('forms.d');
        });

        // Responses
        Route::controller(ResponsesController::class)->group(function () {
            Route::prefix('responses')->name('responses.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('{response}', 'update')->name('update');
                Route::delete('{response}', 'destroy')->name('destroy');
            });

            Route::prefix('forms/{form}/responses')->name('forms.responses.')->group(function () {
                Route::get('getResponsesList', 'getResponsesList')->name('getResponsesList');
                Route::post('RemoveSelectedResponses', 'RemoveSelectedResponses')->name('RemoveSelectedResponses');
                Route::delete('RemoveAllResponses', 'RemoveAllResponses')->name('RemoveAllResponses');
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('{response}', 'update')->name('update');
                Route::delete('{response}', 'destroy')->name('destroy');
            });

            Route::prefix('forms/{form_id}/responses')->name('forms.responses.')->group(function () {
                Route::get('charts', 'ViewCharts')->name('charts');
                Route::get('ExportToExcel', 'ExportToExcel')->name('ExportToExcel');
                Route::get('ExportToPDF', 'ExportToPDF')->name('ExportToPDF');
                Route::get('export/csv', 'csv')->name('csv');
                Route::get('export/excel', 'excel')->name('excel');
                Route::get('{response}/single', 'single_response')->name('single');
                Route::get('{response}/single/form', 'single_response_as_form')->name('single.form');
                Route::get('{response}/single/pdf', 'single_response_pdf')->name('single.pdf');
            });
        });

        // Form workflows
        Route::controller(WorkflowController::class)->group(function () {
            Route::get('forms/{form}/workflow', 'workflowPage')->name('forms.workflow.page');
            Route::get('forms/{form}/workflow/data', 'getWorkflow')->name('forms.workflow.get');
            Route::put('forms/{form}/workflow', 'saveWorkflow')->name('forms.workflow.save');
            Route::get('approvals/users', 'approverUsers')->name('approvals.users');
            Route::get('approvals/inbox', 'inbox')->name('approvals.inbox');
            Route::get('approvals/inbox/data', 'inboxList')->name('approvals.inbox.data');
            Route::post('approvals/{response}/approve', 'approve')->name('approvals.approve');
            Route::post('approvals/{response}/reject', 'reject')->name('approvals.reject');
            Route::get('forms/{form_id}/responses/{response}/approval', 'tracker')->name('forms.responses.approval');
        });
    });
