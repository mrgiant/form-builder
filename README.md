# mrgiant/form-builder

A Laravel form-builder module extracted from the GoldenHospital application:
dynamic forms, questions/fields, responses, and an approval-workflow engine,
with Vue 3 builder components and Blade renderers (including PDF templates).

- **Backend:** `Form`, `Question`, `Answer`, `FormResponse`, plus the workflow
  models (`FormResponseWorkflow`, `FormResponseWorkflowAction`,
  `FormWorkflowNode`, `FormWorkflowEdge`), 5 controllers, and all migrations.
- **Frontend:** a drag-and-drop question builder, a visual workflow builder, 12
  field-type renderers, responses list, and an approvals inbox/tracker.
- **Views:** public form render (`custom_render`), pre-filled render, and PDF
  templates.

## Requirements

- PHP `^8.2`
- Laravel `^12` or `^13`

### Host-provided dependencies

This package was **extracted from a monolith** and is designed to be consumed by
that same application. The controllers and the `Translatable` concern therefore
still reference a handful of classes that the **host app must provide**. These
are intentionally left pointing at the `App\` namespace:

| Referenced class | Used by | Purpose |
| --- | --- | --- |
| `App\Http\Controllers\Controller` | all controllers | base controller (auth/validation traits) |
| `App\Models\User` | `WorkflowController`, `FormResponseWorkflowAction` | approver identity |
| `App\Http\Services\WorkflowRunner` | `AnswerController`, `WorkflowController` | runs the approval workflow |
| `App\Rules\UniqueAnswerResponses` | `AnswerController` | submission validation |
| `App\Notifications\GeneralNotficationMailsAttachment` | `AnswerController` | submit-notification email |
| `App\Exports\FormExport` | `ResponsesController` | Excel export (maatwebsite/excel) |
| `App\Http\Services\ReportsGenerator\PdfFooter` | `ResponsesController` | PDF footer |
| `App\Http\TranslatorAction\Translator`, `App\Models\Translation` | `Concerns\Translatable` | i18n fields |

Fully decoupling these is a follow-up (see **Cutover checklist**). Until then the
package composes cleanly inside the GoldenHospital host.

## Installation (local path repository)

In the host app's `composer.json`:

```json
"repositories": [
    { "type": "path", "url": "../form-builder", "options": { "symlink": true } }
],
```

then:

```bash
composer require mrgiant/form-builder:*
```

The service provider is auto-discovered. It will:

- load the package migrations (deduped by filename against the host's copies),
- register the `form-builder::` view namespace,
- register routes **only if** `config('form-builder.register_routes')` is true
  (off by default to avoid colliding with the host's existing form routes).

Publish what you want to customize:

```bash
php artisan vendor:publish --tag=form-builder-config      # config/form-builder.php
php artisan vendor:publish --tag=form-builder-views       # resources/views/vendor/form-builder
php artisan vendor:publish --tag=form-builder-assets      # resources/js/vendor/form-builder
php artisan vendor:publish --tag=form-builder-migrations  # only for a fresh host
```

## Frontend

The package ships **raw `.vue` source**. Publish it and register the components
on your existing global Vue app:

```js
// resources/js/app.js (host)
import { registerFormBuilder } from './vendor/form-builder';

registerFormBuilder(app, { lazy });   // reuse the host's lazy() wrapper
```

This registers: `forms-index`, `forms-manage-questions`,
`forms-questions-answers`, `forms-responses-index`, `forms-workflow-builder`,
`approvals-inbox`, `approval-tracker`, `form-charts`.

The components require `golden-logic-ui`, `vuedraggable`, and Vue 3 in the host.
They also import a few **host-provided Vue libraries** via the `@` alias
(`@` → `resources/js`), which the host must define in `vite.config.js`:

| Import | Provided by host at |
| --- | --- |
| `@/services/multilingualService` | `resources/js/services/` |
| `@/components/fields_departmns/*` | shared field inputs (also used by other host features) |
| `@/components/fieldsAnswer/*` | shared answer-display inputs |
| `@/components/Charts/Pie.vue` | shared chart components |

These are intentionally **not** bundled so the host owns a single copy (they are
shared with other host features). Re-run the publish command after updating the
package to refresh the copy under `resources/js/vendor/form-builder`.

## Configuration

`config/form-builder.php`:

| Key | Default | Meaning |
| --- | --- | --- |
| `register_routes` | `false` | Let the package own the form routes. |
| `route_prefix` | `admin` | URL prefix for the route group. |
| `route_name_prefix` | `admin.` | Route-name prefix (`admin.forms.*`). |
| `middleware` | `['web', 'auth']` | Middleware for the route group. |
| `user_model` | `App\Models\User` | Approver model. |

## Cutover status (GoldenHospital host)

The cutover has been **performed** in the GoldenHospital host — the package is
the source of truth for the module's models, controllers, and Vue components:

1. ✅ Host `app/Models/{Form,Question,Answer,FormResponse,FormResponse*,FormWorkflow*}.php`
   deleted; all references repointed to `Mrgiant\FormBuilder\Models\*`.
2. ✅ Host `app/Http/Controllers/Admin/{Forms,Questions,Answer,Responses,Workflow}Controller.php`
   deleted; `routes/web.php` + `routes/api.php` now reference the package controllers.
   (The route *definitions* stay in the host so they keep the host's admin
   middleware/locale/gate context; `register_routes` remains `false`.)
3. ✅ Host `resources/js/components/cruds/Forms/**` and `FormCharts.vue` deleted;
   `app.js` registers the components via `registerFormBuilder(app, { lazy })`
   from the published `resources/js/vendor/form-builder`.

Remaining (optional) hardening:

- Blade views still live in the host (the controllers render `admin.forms.*` /
  `forms.*`, which resolve to the host's layouts). The package ships its own
  copies under `form-builder::` for external consumers.
- Decouple the host-provided PHP classes and Vue libraries (tables above) for a
  fully standalone package.

## License

MIT
