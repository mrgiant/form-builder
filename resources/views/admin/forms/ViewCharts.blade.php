@extends('layouts.admin')
@section('content')



@section("styles")

<style>










    </style>


@endsection


<div class="container">



    <div class="card">
        <div class="card-header">View Charts</div>

        <div class="card-body">


<form-charts :data="'{{json_encode($questions)}}'"></form-charts>


        </div>
    </div>

</div>

<br/>




@endsection



@section('scripts1')
@parent





@endsection
