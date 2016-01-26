@extends('templates.login')

@section('content')
<div class="form-top">
    <div class="form-top-left">
        <h3>Recuperar sua senha</h3>
        <p>Digite seu email e você receberá um link para criar uma nova senha:</p>
    </div>
    <div class="form-top-right">
        <i class="fa fa-lock"></i>
    </div>
</div>
<div class="form-bottom">
    @include('includes.errors')

    @include('includes.message', ['message' => Session::get('status')])

    <form role="form" action="{{ action('Auth\PasswordController@postEmail') }}" method="post" class="login-form">
        {!! csrf_field() !!}
        <div class="form-group">
            <label class="sr-only" for="email">Email</label>
            <input type="text" name="email" placeholder="Email..." class="form-email form-control" id="email" value="{{ old('email') }}">
        </div>
        <button type="submit" class="btn">Mandar email</button>
    </form>
</div>
@endsection