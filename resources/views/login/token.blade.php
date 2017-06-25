@extends('templates.login')

@section('content')
    <div class="form-top">
        <div class="form-top-left">
            <h3>Você já tem uma chave Caronaê!</h3>
        </div>
    </div>
    <div class="form-bottom">
        <p class="text-center">Sua chave de acesso ao Caronaê é:</p>
        <h2 class="text-center token" data-clipboard-text="{{ $user->token }}">{{ $user->token }}</h2>
        <p class="text-center copy-text">Basta clicar para copiar a chave.</p>

        <form method="POST">
            <div class="form-group">
                <input type="hidden" name="user" id="user" value="<?= $user->id_ufrj ?>">
                <input type="hidden" name="app_token" id="app_token" value="<?= $user->token ?>">
            </div>
        </form>
    </div>
@endsection

@section('after_scripts')
    <script src="{{ asset('vendor/clipboard.min.js') }}"></script>
    <script src="{{ asset('js/chave.js') }}"></script>
@endsection