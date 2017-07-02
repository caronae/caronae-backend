@extends('templates.login')

@section('content')
    <div class="form-top">
        <div class="form-top-left">
            <h3>
                @if($displayTermsOfUse)
                    Termos e condições de uso
                @else
                    Você já tem uma chave Caronaê!
                @endif
            </h3>
        </div>
    </div>
    <div class="form-bottom">
        <form>
            @if($displayTermsOfUse)
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="acceptedTermsOfUse" value="1">

                <div class="terms-alert">
                    <span class="icon glyphicon glyphicon-warning-sign"></span>
                    <h2>Você já leu nossos termos e condições de uso?</h2>
                    <p>
                        Para obter sua chave de acesso, você deve ler e concordar com nossos termos e condições de uso.
                    </p>

                    <button type="submit" class="button btn btn-block btn-primary" onclick="return openTermsOfUse()">
                        <span class="glyphicon glyphicon-list-alt"></span>
                        <span>Ler termos de uso</span>
                    </button>

                    <button type="submit" class="button btn btn-block btn-success">
                        <span class="glyphicon glyphicon-ok"></span>
                        <span>Li e aceito os termos</span>
                    </button>
                </div>
            @else
                <input type="hidden" name="user" id="user" value="<?= $user->id_ufrj ?>">
                <input type="hidden" name="app_token" id="app_token" value="<?= $user->token ?>">

                <p class="text-center">Sua chave de acesso ao Caronaê é:</p>
                <h2 class="text-center token" data-clipboard-text="{{ $user->token }}">{{ $user->token }}</h2>
                <p class="text-center copy-text">Basta clicar para copiar a chave.</p>
            @endif
        </form>
    </div>
@endsection

@section('after_scripts')
    <script src="{{ asset('vendor/clipboard.min.js') }}"></script>
    <script src="{{ asset('js/chave.js') }}"></script>
@endsection