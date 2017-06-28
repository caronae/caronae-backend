@extends('templates.login')

@section('content')
    <div class="box-institutions">
        <h1>Selecione sua instituição</h1>

        <ul class="institutions">
            @foreach ($institutions as $institution)
                <li>
                    <a href="?institution={{ $institution->authentication_url }}">{{ $institution->name }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endsection