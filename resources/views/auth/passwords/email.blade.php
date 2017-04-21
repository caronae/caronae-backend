@extends('templates.login')

@section('content')
<div class="form-top">
    <div class="form-top-left">
        <h3>Redefinir senha</h3>
        <p>Digite o seu email para continuar:</p>
    </div>
    <div class="form-top-right">
        <i class="fa fa-lock"></i>
    </div>
</div>

<div class="form-bottom">
    @include('includes.errors')
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form class="login-form" role="form" method="POST" action="{{ route('password.email') }}">
        {{ csrf_field() }}

        <div class="form-group">
            <label for="email" class="sr-only">E-mail</label>
            <input id="email" type="email" placeholder="E-mail" class="form-email form-control" name="email" value="{{ old('email') }}" required>
        </div>

        <button type="submit" class="btn">
            Enviar e-mail de recuperação
        </button>
    </form>
</div>

@endsection
