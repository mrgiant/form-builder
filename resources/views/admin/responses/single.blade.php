@extends('layouts.admin')
@section('content')

<approval-tracker :form_id="'{{ $form->id }}'" :response_id="'{{ $response_id }}'"></approval-tracker>

<div class="rounded-xl bg-white dark:bg-gray-900 border border-stroke dark:border-white/10 shadow-default overflow-hidden">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-stroke dark:border-white/10">
        <div class="flex items-center gap-3">
            <a href="/admin/forms/{{ $form->id }}/responses"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primaryDark">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Responses
            </a>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <span class="font-semibold text-gray-800 dark:text-white">{{ $form->name }}</span>
        </div>
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-gray-700 text-white hover:bg-gray-600 transition-colors print:hidden">
            <i class="fa-solid fa-print"></i> Print
        </button>
    </div>

    {{-- Answers --}}
    <div class="divide-y divide-stroke dark:divide-white/10 px-6">
        @forelse($questions as $qid => $label)
            <div class="grid grid-cols-12 gap-4 py-4">
                <div class="col-span-12 sm:col-span-4">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</p>
                </div>
                <div class="col-span-12 sm:col-span-8">
                    @php $answer = $data[$qid] ?? ''; @endphp
                    @if($answer !== '' && $answer !== null)
                        @if(is_string($answer) && preg_match('~^https?://~i', $answer))
                            <a href="{{ $answer }}" target="_blank" rel="noopener"
                                class="text-sm text-primary hover:underline break-all inline-flex items-center gap-1">
                                <i class="fa-solid fa-up-right-from-square text-xs"></i>
                                <span class="break-all">{{ $answer }}</span>
                            </a>
                        @else
                            <p class="text-sm text-gray-900 dark:text-white">{{ $answer }}</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic">— No answer —</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="py-8 text-center text-gray-400 dark:text-gray-500">No questions found.</p>
        @endforelse
    </div>

</div>

@endsection
