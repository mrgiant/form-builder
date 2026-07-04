<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $form->name }} — Response</title>
    <style>
        :root {
            --gl-primary: {{ config('app.primary_color') }};
            --gl-primary-dark: {{ adjustColorForModeNew(config('app.primary_color'), 0.3) }};
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 32px;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: #1f2d3d;
            font-size: 13px;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid var(--gl-primary);
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 { margin: 0 0 4px; font-size: 20px; color: var(--gl-primary-dark); }
        .header .meta { color: #64748b; font-size: 12px; }
        .row {
            display: flex;
            gap: 16px;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .row .label { width: 34%; font-weight: 600; color: #334155; }
        .row .value { width: 66%; color: #0f172a; word-break: break-word; }
        .value a { color: var(--gl-primary); }
        .value .empty { color: #94a3b8; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $form->name }}</h1>
        <div class="meta">
            Response submitted {{ $submitted_at ? \Carbon\Carbon::parse($submitted_at)->format('d-m-Y H:i') : '' }}
        </div>
    </div>

    @forelse($questions as $qid => $label)
        @php $answer = $data[$qid] ?? ''; @endphp
        <div class="row">
            <div class="label">{{ $label }}</div>
            <div class="value">
                @if($answer !== '' && $answer !== null)
                    @if(is_string($answer) && preg_match('~^https?://~i', $answer))
                        <a href="{{ $answer }}" target="_blank" rel="noopener">{{ $answer }}</a>
                    @else
                        {{ $answer }}
                    @endif
                @else
                    <span class="empty">— No answer —</span>
                @endif
            </div>
        </div>
    @empty
        <p>No questions found.</p>
    @endforelse
</body>
</html>
