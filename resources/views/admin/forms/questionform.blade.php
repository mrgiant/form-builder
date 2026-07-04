






@include('formfields.text', ['content' => '' ,'name'=>'label','show'=>false,'field_name'=>'Label','type'=>'text','required'=>true])

<?php   $type=["text","textarea","select","checkbox-list"]   ?>
@include('formfields.select2_single', ['content' => "" ,'name'=>'question_type','show'=>false,'field_name'=>"Type",'required'=>true,'arrays_data'=>$type,'title'=>'title'])

<div id="option-area">
@include('formfields.textareia', ['content' => '' ,'name'=>'options','show'=>false,'field_name'=>'Options for question types that need them','type'=>'text','required'=>false])
</div>


@include('formfields.checkbox', ['content' => 0 ,'name'=>'required','show'=>false,'field_name'=>'Required?'])


@include('formfields.text', ['content' => '' ,'name'=>'css_class','show'=>false,'field_name'=>'CSS Class','type'=>'text','required'=>false])


@section('scripts2')
@parent


<script type="module">
    window.onload =function() {
      var optionfields = ["select", "checkbox-list", "section"];

      if(optionfields.includes($('#question_type option:selected').text())) {
        $('#option-area').show();
      } else {
        $('#option-area').hide();
        $('#options').val("");
      }

     $('#question_type').change(function() {
       if(optionfields.includes($('#question_type option:selected').text())) {
         $('#option-area').show();
       } else {
         $('#option-area').hide();
         $('#options').val("");
       }
     });

     $('#label').focus();
    }
  </script>



@endsection

