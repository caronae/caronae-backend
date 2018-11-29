@extends('templates.login')

@section('title', "Carona de $driver - $title - Caronaê")

@section('content')
    <div class="form-top">
        <div class="form-top-left">
            <h3>{{ $title }}</h3>
        </div>
    </div>
    <div class="form-bottom ride-content">
        <p class="ride-driver">Carona oferecida por <strong>{{ $driver }}</strong>.</p>

        <section class="open-app-section">
            <p>Veja esta carona no app do Caronaê.</p>

            <a href="{{ $deepLinkUrl }}" class="button btn btn-success open-app-button">
                <span>Abrir no Caronaê</span>
            </a>
        </section>

        <section class="store-links">
            <p>Ainda não tem o app?</p>

            <a href="https://itunes.apple.com/us/app/caronae-ufrj-o-sistema-oficial/id1078790049?ls=1&mt=8"
               class="store-button">
                <img src="{{ asset('images/appstore.png') }}">
            </a>
            <a href="https://play.google.com/store/apps/details?id=br.ufrj.caronae" class="store-button">
                <img src="{{ asset('images/googleplay.png') }}">
            </a>
        </section>
    </div>
@endsection

@section('head-tags')
    <meta property="og:title" content="{{ "Carona de $title" }}">
    <meta property="og:description" content="{{ "Pegue uma carona com $driver pelo Caronaê!" }}">
    <meta property="og:image" content="{{ asset('images/logo_caronae.png') }}">
@endsection
