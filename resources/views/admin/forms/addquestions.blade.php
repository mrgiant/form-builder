@extends('layouts.admin')
@section('content')
<forms-manage-questions :form_id="'{{ $form->id }}'"></forms-manage-questions>
@endsection
