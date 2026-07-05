@extends('layouts.admin')

@section('styles')
<style>
    @page {
        margin: 12mm;
    }

    @media print {
        /* Isolate the response so the admin sidebar/topbar are not printed. */
        body * { visibility: hidden; }
        #printable-response, #printable-response * { visibility: visible; }
        #printable-response {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        /* Force browsers to print background colours and images so the
           header, filled radios/checkboxes and coloured cells survive. */
        #printable-response, #printable-response * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Avoid ugly splits through table rows, images and grouped fields. */
        #printable-response tr,
        #printable-response img,
        #printable-response fieldset { break-inside: avoid; }

        #printable-response thead { display: table-header-group; }
    }
</style>
@endsection

@section('content')

<div id="printable-response" class="rounded-xl bg-white dark:bg-gray-900 border border-stroke dark:border-white/10 shadow-default overflow-hidden">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-stroke dark:border-white/10 print:hidden">
        <div class="flex items-center gap-3">
            <a href="/admin/forms/{{ $form->id }}/responses"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primaryDark">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Responses
            </a>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <span class="font-semibold text-gray-800 dark:text-white">{{ $form->name }}</span>
            @if($submitted_at)
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    submitted {{ \Carbon\Carbon::parse($submitted_at)->format('d-m-Y H:i') }}
                </span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="/admin/forms/{{ $form->id }}/responses/{{ $response_id }}/single/pdf"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <i class="fa-solid fa-file-pdf"></i> Download PDF
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-gray-700 text-white hover:bg-gray-600 transition-colors">
                <i class="fa-solid fa-print"></i> Print
            </button>
        </div>
    </div>

    {{-- Response rendered as the form (read-only) --}}
    <div class="p-6">
        <forms-questions-answers
            :data='@json($data)'
            :form_id="'{{ $form->id }}'"
            :show="true">
        </forms-questions-answers>
    </div>

</div>

@endsection
