<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $form->name }}</title>

    @if (! empty(config('form-builder.public_assets')))
        @vite(config('form-builder.public_assets'))
    @endif

    <style>
        body { margin: 0; background: #f8fafc; color: #0f172a; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        .gl-public-wrap { max-width: 820px; margin: 0 auto; padding: 2rem 1rem 4rem; }
        .gl-public-header { text-align: center; margin-bottom: 2rem; }
        .gl-public-header h1 { font-size: 1.75rem; margin: 0 0 .5rem; }
        .gl-public-header .gl-desc { color: #64748b; font-size: .95rem; }
        .gl-state { max-width: 560px; margin: 3rem auto; padding: 2rem; text-align: center;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; color: #475569; }
    </style>
</head>

<body>
    <div class="gl-public-wrap">
        <header class="gl-public-header">
            <h1>{{ $form->name }}</h1>
            @if (! empty($form->description))
                <div class="gl-desc">{!! $form->description !!}</div>
            @endif
        </header>

        @if ($state === 'not_started')
            <div class="gl-state">{!! $form->not_start_message ?: 'This form is not open yet.' !!}</div>
        @elseif ($state === 'closed')
            <div class="gl-state">{!! $form->close_message ?: 'This form is closed.' !!}</div>
        @else
            {{-- The host's compiled bundle (public_assets) registers <forms-questions-answers>
                 via registerFormBuilder() and mounts the Vue app on #app. --}}
            <div id="app">
                <forms-questions-answers
                    :data='[]'
                    :form_id="'{{ $form->id }}'"
                    :show="false"
                ></forms-questions-answers>
            </div>
        @endif
    </div>
</body>

</html>
