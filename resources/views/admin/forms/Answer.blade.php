@extends('layouts.admin')
@section('content')

<div class="rounded-xl bg-white dark:bg-gray-900 shadow-default border border-stroke dark:border-white/10 overflow-hidden">

    {{-- Header Banner --}}
    <div class="px-6 py-10 text-center border-b border-stroke dark:border-white/10 bg-gray-800 dark:bg-gray-950">
        <h2 class="text-3xl font-bold text-white">{{ $form->name }}</h2>
        @if($form->description)
            <p class="text-gray-300 mt-2 text-sm">{!! $form->description !!}</p>
        @endif
    </div>

    {{-- Questions --}}
    <div class="p-6">
        <forms-questions-answers
            :data='[]'
            :form_id="'{{ $form->id }}'"
            :show_number="'{{ $form->show_number == 1 ? 'true' : 'false' }}'"
            :show="false"
        ></forms-questions-answers>
    </div>

</div>

@endsection
