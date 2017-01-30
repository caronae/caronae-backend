<form role="form">
  {{-- Show the erros, if any --}}
  @if ($errors->any())
  	<div class="col-md-12">
  		<div class="callout callout-danger">
	        <h4>{{ trans('backpack::crud.please_fix') }}</h4>
	        <ul>
			@foreach($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
			</ul>
		</div>
  	</div>
  @endif

  {{-- Show the inputs --}}
  @foreach ($fields as $field)
    <!-- load the view from the application if it exists, otherwise load the one in the package -->
	@if(view()->exists('vendor.backpack.crud.fields.'.$field['type']))
		@include('vendor.backpack.crud.fields.'.$field['type'], array('field' => $field))
	@else
		@include('crud::fields.'.$field['type'], array('field' => $field))
	@endif
  @endforeach
</form>

{{-- Define blade stacks so css and js can be pushed from the fields to these sections. --}}

@section('after_styles')
	<!-- CRUD FORM CONTENT - crud_fields_styles stack -->
	@stack('crud_fields_styles')
@endsection

@section('after_scripts')
	<!-- CRUD FORM CONTENT - crud_fields_scripts stack -->
	@stack('crud_fields_scripts')

	<script>
        jQuery('document').ready(function($){

      		// Ctrl+S and Cmd+S trigger Save button click
      		$(document).keydown(function(e) {
      		    if ((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey))
      		    {
      		        e.preventDefault();
      		        // alert("Ctrl-s pressed");
      		        $("button[type=submit]").trigger('click');
      		        return false;
      		    }
      		    return true;
      		});

          @if( $crud->autoFocusOnFirstField )
            //Focus on first field
            @php
              $focusField = array_first($fields, function($field){
                  return isset($field['auto_focus']) && $field['auto_focus'] == true;
              })
            @endphp

            @if($focusField)
              window.focusField = $('[name="{{$focusField['name']}}"]').eq(0),
            @else
              var focusField = $('form').find('input, textarea, select').not('[type="hidden"]').eq(0),
            @endif

            fieldOffset = focusField.offset().top,
            scrollTolerance = $(window).height() / 2;

            focusField.trigger('focus');

            if( fieldOffset > scrollTolerance ){
                $('html, body').animate({scrollTop: (fieldOffset - 30)});
            }
          @endif
        });
	</script>
@endsection
