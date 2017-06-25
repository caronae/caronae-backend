@extends('templates.login')

@section('content')
    <div class="form-top">
        <div class="form-top-left">
            <h3>Caronaê</h3>
            <p>Selecione sua instituição:</p>
        </div>
        <div class="form-top-right">
            <i class="fa fa-lock"></i>
        </div>
    </div>
    <div class="form-bottom">
        @include('includes.errors')
        <form role="form" action="" class="login-form">
            <select name="institution">
                @foreach ($institutions as $institution)
                    <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn">Selecionar</button>
        </form>
    </div>
@endsection