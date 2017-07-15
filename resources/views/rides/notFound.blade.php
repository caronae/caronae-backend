@extends('templates.login')

@section('content')
    <div class="form-top">
        <div class="form-top-left">
            <h3>Ops! Carona não encontrada</h3>
        </div>
    </div>
    <div class="form-bottom ride-content">
        <p><strong>Não conseguimos encontrar essa carona.</strong> É possível que o motorista tenha a cancelado.</p>

        <section class="store-links">
            <p>Veja outras caronas no nosso app:</p>

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