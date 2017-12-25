@extends('templates.base')

@section('body')
<div class="home">
    <img src="{{ asset('images/logo_caronae_with_text.png') }}" class="logo">

    <ul class="links">
        <li><a href="{{ route('home') }}">Área administrativa &raquo;</a></li>
    </ul>
</div>
@endsection