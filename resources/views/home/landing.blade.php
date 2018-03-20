@extends('templates.base')

@section('body')
<div class="home">
    <div class="logo">
        <img src="{{ asset('images/logo_caronae_with_text.png') }}" class="logo-img">
    </div>

    <ul class="links">
        <li><a href="{{ route('home') }}">Área administrativa &raquo;</a></li>
    </ul>
</div>
@endsection