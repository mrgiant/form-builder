<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ str_replace('_', '-', app()->getLocale()) == 'ar' ? 'rtl' : 'ltr' }}">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ getTranslationField($form, 'name') }}</title>
    {!! getTranslationField($form, 'custom_head') !!}
    <style>
        .gl-submitting { opacity: .65; cursor: not-allowed; }
        .gl-spinner {
            display: inline-block;
            width: 1em;
            height: 1em;
            margin-inline-end: .5em;
            vertical-align: -.15em;
            border: 2px solid currentColor;
            border-top-color: transparent;
            border-radius: 50%;
            animation: gl-spin .6s linear infinite;
        }
        @keyframes gl-spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div id="gl-form-root"
        data-submit-url="{{ url('forms/'.$form->id.'/d') }}">
        {!! getTranslationField($form, 'custom_html') !!}
    </div>

    <template id="gl-form-thanks">
        <div style="max-width:640px;margin:3rem auto;padding:2rem;text-align:center;font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;">
            @if(!empty(getTranslationField($form, 'thank_you_message')))
                {!! getTranslationField($form, 'thank_you_message') !!}
            @else
                <h2 style="font-size:1.4rem;margin:0 0 .5rem;">Thank you!</h2>
                <p style="color:#64748b;margin:0;">Your response has been submitted successfully.</p>
            @endif
        </div>
    </template>

    {{-- The form's own scripts (interactive wizards etc.) run first. Stored as raw <script> tags. --}}
    {!! getTranslationField($form, 'custom_js') !!}

    <script>
        (function () {
            var root = document.getElementById('gl-form-root');
            if (!root) { return; }
            var form = root.querySelector('form');
            if (!form) { return; }

            var submitUrl = root.getAttribute('data-submit-url');
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            var token = tokenMeta ? tokenMeta.getAttribute('content') : '';
            var errorBox = null;
            var submitting = false;

            // Capture phase + stopImmediatePropagation overrides the form's own
            // submit handler so answers always POST to our endpoint.
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (submitting) { return; }
                submitting = true;
                if (errorBox) { errorBox.style.display = 'none'; errorBox.innerHTML = ''; }

                var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                buttons.forEach(setButtonLoading);

                fetch(submitUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                }).then(function (res) {
                    if (res.ok) {
                        showThanks();
                        return null;
                    }
                    return res.json().catch(function () { return {}; }).then(function (data) {
                        showErrors(data);
                        buttons.forEach(clearButtonLoading);
                        submitting = false;
                    });
                }).catch(function () {
                    showErrors({});
                    buttons.forEach(clearButtonLoading);
                    submitting = false;
                });
            }, true);

            function setButtonLoading(b) {
                b.disabled = true;
                b.classList.add('gl-submitting');
                var spinner = document.createElement('span');
                spinner.className = 'gl-spinner';
                spinner.setAttribute('aria-hidden', 'true');
                if (b.tagName.toLowerCase() === 'button') {
                    b.insertBefore(spinner, b.firstChild);
                }
            }

            function clearButtonLoading(b) {
                b.disabled = false;
                b.classList.remove('gl-submitting');
                var spinner = b.querySelector('.gl-spinner');
                if (spinner) { spinner.remove(); }
            }

            function showThanks() {
                var tpl = document.getElementById('gl-form-thanks');
                root.innerHTML = tpl ? tpl.innerHTML : '<div style="text-align:center;padding:3rem;">Thank you! Your response has been submitted.</div>';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function showErrors(data) {
                var msgs = [];
                if (data && data.errors) {
                    Object.keys(data.errors).forEach(function (k) {
                        var v = data.errors[k];
                        msgs = msgs.concat(Array.isArray(v) ? v : [v]);
                    });
                }
                if (!msgs.length) {
                    msgs = ['Something went wrong. Please review the form and try again.'];
                }
                if (!errorBox) {
                    errorBox = document.createElement('div');
                    errorBox.id = 'gl-form-errors';
                    form.insertBefore(errorBox, form.firstChild);
                }
                errorBox.style.cssText = 'display:block;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:14px;';
                var html = '<ul style="margin:0;padding-left:18px;">';
                msgs.forEach(function (m) {
                    html += '<li>' + String(m).replace(/</g, '&lt;') + '</li>';
                });
                html += '</ul>';
                errorBox.innerHTML = html;
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        })();
    </script>
</body>
</html>
