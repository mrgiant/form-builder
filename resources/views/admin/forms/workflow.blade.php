@extends('layouts.admin')

@section('content')
    <forms-workflow-builder :form_id="'{{ $form->id }}'"></forms-workflow-builder>
@endsection
