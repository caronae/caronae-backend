@extends('templates.login')

@section('content')
    <div class="form-top">
        <div class="form-top-left">
            <h3>Selecione sua instituição:</h3>
        </div>
    </div>
    <div class="form-bottom">
        <ul class="nav nav-pills institutions">
            @foreach ($institutions as $institution)
                <li>
                    <a href="{{ $institution->authentication_url }}">{{ $institution->name }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endsection