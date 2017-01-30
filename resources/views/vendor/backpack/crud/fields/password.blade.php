<!-- password -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    <input
    	type="password"
    	name="{{ $field['name'] }}"
        @include('crud::inc.field_attributes')
    	>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>