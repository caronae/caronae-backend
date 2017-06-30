@extends('templates.login')

@section('content')
    <div class="form-top">
        <div class="form-top-left">
            <h3>Sua instituição não autorizou seu acesso ao Caronaê.</h3>
        </div>
    </div>
    <div class="form-bottom">
        <p class="text-center">{{ $error }}</p>
    </div>
@endsection
