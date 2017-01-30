<!-- view field -->

<div @include('crud::inc.field_wrapper_attributes') >
  @include($field['view'], compact('crud', 'entry', 'field'))
</div>
