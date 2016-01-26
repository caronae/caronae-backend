@if ($errors->any())
    @foreach($errors->all() as $error)
        <p class="alert alert-danger">
            {{ $error }}
        </p>
    @endforeach
@endif