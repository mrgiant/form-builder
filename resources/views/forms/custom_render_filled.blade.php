<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ getTranslationField($form, 'name') }} — Response</title>
    {!! getTranslationField($form, 'custom_head') !!}
    <style>
        :root {
            --gl-primary: {{ config('app.primary_color') }};
            --gl-primary-dark: {{ adjustColorForModeNew(config('app.primary_color'), 0.3) }};
        }
        .gl-print-bar {
            position: sticky;
            top: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 16px;
            background: var(--gl-primary);
            color: #fff;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            font-size: 14px;
        }
        .gl-print-bar .gl-actions { display: flex; gap: 8px; }
        .gl-print-bar button, .gl-print-bar a {
            font: inherit;
            padding: 6px 14px;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.35);
            background: transparent;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
        }
        .gl-print-bar button.primary { background: var(--gl-primary-dark); border-color: var(--gl-primary-dark); }
        #gl-form-root :is(input, textarea, select, button) { pointer-events: none; }

        /* Full-width override for the response view (the live form keeps its own layout) */
        body { padding: 0 !important; margin: 0 !important; }
        #gl-form-root { width: 100%; }
        #gl-form-root > * {
            max-width: none !important;
            width: auto !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-radius: 0 !important;
        }

        @page {
            margin: 12mm;
        }

        @media print {
            .gl-print-bar { display: none !important; }

            /* Force browsers to print background colours and images so the
               header, filled radios/checkboxes and coloured cells survive. */
            html, body, #gl-form-root, #gl-form-root * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Avoid ugly splits through table rows, images and grouped fields. */
            #gl-form-root tr,
            #gl-form-root img,
            #gl-form-root fieldset,
            #gl-form-root thead { break-inside: avoid; }

            #gl-form-root thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <div class="gl-print-bar">
        <span>{{ getTranslationField($form, 'name') }} — submitted {{ $submitted_at ? \Carbon\Carbon::parse($submitted_at)->format('d-m-Y H:i') : '' }}</span>
        <span class="gl-actions">
            <a href="{{ url('admin/forms/'.$form->id.'/responses') }}">Back</a>
            <a href="{{ url('admin/forms/'.$form->id.'/responses/'.$response_id.'/single/pdf') }}">Download PDF</a>
            <button type="button" class="primary" onclick="window.print()">Print</button>
        </span>
    </div>

    <div id="gl-form-root">
        {!! getTranslationField($form, 'custom_html') !!}
    </div>

    <script>
        window.__glAnswers = @json($answers);

        (function () {
            var answers = window.__glAnswers || {};
            var root = document.getElementById('gl-form-root');
            if (!root) { return; }
            var scope = root.querySelector('form') || root;

            function asText(val) {
                if (Array.isArray(val)) { return val.join(', '); }
                return val === null || val === undefined ? '' : String(val);
            }

            function inList(val, candidate) {
                if (Array.isArray(val)) {
                    return val.some(function (v) { return String(v) === String(candidate); });
                }
                return String(val) === String(candidate);
            }

            Object.keys(answers).forEach(function (key) {
                var val = answers[key];
                var els = scope.querySelectorAll('[name="' + key + '"], [name="' + key + '[]"]');

                els.forEach(function (el) {
                    var tag = el.tagName.toLowerCase();
                    var type = (el.getAttribute('type') || '').toLowerCase();

                    if (tag === 'select') {
                        Array.prototype.forEach.call(el.options, function (o) {
                            o.selected = inList(val, o.value) || inList(val, o.textContent.trim());
                        });
                    } else if (type === 'checkbox' || type === 'radio') {
                        el.checked = inList(val, el.value);
                    } else if (type === 'file') {
                        if (asText(val)) {
                            var link = document.createElement('a');
                            link.href = asText(val);
                            link.target = '_blank';
                            link.rel = 'noopener';
                            link.textContent = 'View uploaded file';
                            link.style.cssText = 'display:inline-block;color:var(--gl-primary);text-decoration:underline;';
                            el.parentNode.insertBefore(link, el.nextSibling);
                        }
                        el.style.display = 'none';
                    } else if (tag === 'textarea') {
                        el.textContent = asText(val);
                        el.value = asText(val);
                    } else {
                        el.setAttribute('value', asText(val));
                        el.value = asText(val);
                    }
                });
            });

            // Read-only: hide action buttons, disable/lock the rest, drop required.
            scope.querySelectorAll('input, textarea, select, button').forEach(function (el) {
                var tag = el.tagName.toLowerCase();
                var type = (el.getAttribute('type') || '').toLowerCase();
                if (tag === 'button' || type === 'submit' || type === 'reset' || type === 'button') {
                    el.style.display = 'none';
                } else if (tag === 'select' || type === 'checkbox' || type === 'radio' || type === 'file') {
                    el.disabled = true;
                } else {
                    el.readOnly = true;
                }
                el.removeAttribute('required');
            });

            // Flatten interactive layouts (multi-step wizards): reveal every
            // container that holds a real field so all answers are visible at once.
            root.querySelectorAll('*').forEach(function (el) {
                var field = el.querySelector('input:not([type=hidden]), textarea, select');
                if (!field) { return; }
                if (window.getComputedStyle(el).display === 'none') {
                    el.style.setProperty('display', 'block', 'important');
                }
            });
        })();
    </script>
</body>
</html>
