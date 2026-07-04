@extends('layouts.admin')
@section('content')

<forms-responses-index
    :form_id="'{{ $form->id }}'"
    :form_name="'{{ addslashes($form->name) }}'">
</forms-responses-index>

@endsection
