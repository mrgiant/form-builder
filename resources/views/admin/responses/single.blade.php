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
                            @php
                                $isDownload = str_contains($answer, '/downloadAttachment/');
                                $ext = strtolower(pathinfo(parse_url($answer, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                                // Decode the stored path and strip the uniqid_ prefix to show the real filename.
                                $fileName = $isDownload
                                    ? preg_replace('/^[0-9a-f]{12,15}_/i', '', basename(str_replace('slash_mw_attachment', '/', $answer)))
                                    : $answer;
                                $iconMap = [
                                    'jpg'=>'fa-file-image','jpeg'=>'fa-file-image','png'=>'fa-file-image','gif'=>'fa-file-image','webp'=>'fa-file-image','svg'=>'fa-file-image',
                                    'pdf'=>'fa-file-pdf','doc'=>'fa-file-word','docx'=>'fa-file-word',
                                    'xls'=>'fa-file-excel','xlsx'=>'fa-file-excel','csv'=>'fa-file-csv',
                                    'ppt'=>'fa-file-powerpoint','pptx'=>'fa-file-powerpoint',
                                    'zip'=>'fa-file-zipper','rar'=>'fa-file-zipper',
                                    'mp4'=>'fa-file-video','mov'=>'fa-file-video','webm'=>'fa-file-video',
                                    'mp3'=>'fa-file-audio','wav'=>'fa-file-audio','txt'=>'fa-file-lines',
                                ];
                                $fileIcon = $iconMap[$ext] ?? 'fa-file';
                            @endphp
                            @if($isDownload)
                                <a href="{{ $answer }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-2 max-w-full px-3 py-2 rounded-lg border border-stroke dark:border-white/10 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                                    <i class="fa-solid {{ $fileIcon }} text-primary"></i>
                                    <span class="text-sm text-gray-800 dark:text-gray-100 truncate max-w-[18rem]">{{ $fileName ?: 'Attachment' }}</span>
                                    <i class="fa-solid fa-download text-xs text-gray-400 group-hover:text-primary"></i>
                                </a>
                            @else
                                <a href="{{ $answer }}" target="_blank" rel="noopener"
                                    class="text-sm text-primary hover:underline break-all inline-flex items-center gap-1">
                                    <i class="fa-solid fa-up-right-from-square text-xs"></i>
                                    <span class="break-all">{{ $answer }}</span>
                                </a>
                            @endif
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
