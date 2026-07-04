/**
 * Form Builder — Vue component registration helper.
 *
 * This package ships raw .vue source. Publish it into the host app with:
 *
 *     php artisan vendor:publish --tag=form-builder-assets
 *
 * which copies this folder to resources/js/vendor/form-builder. Then, in the
 * host's app.js, register the components on the existing global Vue app:
 *
 *     import { registerFormBuilder } from './vendor/form-builder';
 *     registerFormBuilder(app, { lazy });   // reuse the host's lazy() wrapper
 *
 * The components depend on the `golden-logic-ui` package (Form, inputs,
 * GlDataTable*) and `vuedraggable` being available in the host — the same
 * dependencies the module used before extraction. They are intentionally NOT
 * bundled here so the host owns a single copy.
 *
 * @param {import('vue').App} app         The Vue application instance.
 * @param {object}   [options]
 * @param {Function} [options.lazy]       Wrapper turning () => import() into a
 *                                        component (e.g. defineAsyncComponent
 *                                        plus the host's boot-splash counter).
 *                                        Falls back to defineAsyncComponent.
 */
import { defineAsyncComponent } from 'vue';

export function registerFormBuilder(app, { lazy } = {}) {
    const load = lazy ?? ((loader) => defineAsyncComponent(loader));

    const components = {
        'forms-index': () => import('./components/cruds/Forms/Index.vue'),
        'forms-manage-questions': () => import('./components/cruds/Forms/ManageQuestions.vue'),
        'forms-questions-answers': () => import('./components/cruds/Forms/QuestionsAnswers.vue'),
        'forms-responses-index': () => import('./components/cruds/Forms/Responses/Index.vue'),
        'forms-workflow-builder': () => import('./components/cruds/Forms/WorkflowBuilder.vue'),
        'approvals-inbox': () => import('./components/cruds/Forms/Approvals/Inbox.vue'),
        'approval-tracker': () => import('./components/cruds/Forms/Approvals/Tracker.vue'),
        'form-charts': () => import('./components/FormCharts.vue'),
    };

    for (const [tag, loader] of Object.entries(components)) {
        app.component(tag, load(loader));
    }

    return app;
}

export default registerFormBuilder;
