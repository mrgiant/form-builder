 <!DOCTYPE html>
 <html lang="en">

<head>
     <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>{{$form->name}}</title>

  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet"/>
     <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet"/>
     <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css"
           rel="stylesheet"/>

     <link href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" rel="stylesheet"/>
     <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet"/>
     <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.css" rel="stylesheet"/>
     <link href="{{ asset('css/custom.css') }}" rel="stylesheet"/>





     <style>



.content-container{
    padding: 25px;
    padding-bottom:25px;
    margin-bottom:20px;
    background-color:white;

    box-shadow: 0px 0 7px -5.5px;
    border-radius: 8px;
}




body {
    background: #EAF2F6 !important;
}
label:not(.form-check-label):not(.custom-file-label) {
    font-weight: 700;
}



        {!! $form->css !!}
     </style>
 </head>

 <body>

        <nav class="navbar fixed-top navbar-light bg-light">
                <a class="navbar-brand" href="#">Fixed top</a>
              </nav>






<div class="container" style="padding-top: 70px">


        <div class="content-container text-left" style="font-size: 32px;
        font-weight: 400;
        line-height: 40px;
        color: #202124;">


                <h1>{{$form->name}}</h1>
                <p>{{$form->description or ''}}</p>


                    </div>



 <div class="content-container">




      <div class="panel panel-default">
        <div class='panel-body'>
      @include('admin.forms.errors')
      <form role="form" method="POST" action=" {{ route("admin.forms.d.store", $form->id) }}">
        @csrf

        <input type='hidden' name='form_id' value="{{$form->id}}" />
        @if($form->return_url)
          <input type='hidden' name='return_url' value="{{$form->return_url}}" />
        @endif
          @foreach($form->questions as $q)


              @include('admin.forms.field.' . $q->question_type)



          @endforeach
          <div class='form-group col-md-12'>
            <button type="submit" class="btn btn-primary">
                Submit
            </button>
          </div>
      </form>
    </div>
    </div>





              </div>


              <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.js"></script>
  <script src="{{ asset('js/main.js') }}"></script>
 </body>

 </html>
