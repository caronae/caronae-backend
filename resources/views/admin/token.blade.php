@extends('templates.admin')

@section('header')
    <section class="content-header">
        <h1>
            Self-service API Tokens
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ backpack_url('home') }}">{{ config('backpack.base.project_name') }}</a></li>
            <li class="active">{{ trans('backpack::base.dashboard') }}</li>
        </ol>
    </section>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    @if($caronaeUser)
                        <form action="{{ route('self-service-token-new') }}" method="post">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-primary">Gerar novo token</button>
                        </form>
                    @endif
                </div>

                <div class="box-body">
                    <p class="self-service-token-header">
                        Crie aqui tokens JWT para usar com a API do Caronaê.
                        @if($caronaeUser)
                            Seu usuário do Caronaê é <a href="{{ backpack_url('users') . '/' . $caronaeUser->id }}">{{ $caronaeUser->name }}</a>.
                        @else
                            <div class="alert alert-error">Você não associou um usuário do Caronaê ao seu usuário da área administrativa.</div>
                        @endif
                    </p>

                    @if($caronaeUser)
                        @forelse($tokens as $token)
                            <div class="api-token-block">
                                <b>Gerado em:</b> {{ $token['issued_at'] }}
                                <b>Expira em:</b> {{ $token['expiration'] }}<br>
                                <pre class="api-token">{{ $token['token'] }}</pre>
                            </div>
                        @empty
                            Você não gerou nenhum token.
                        @endforelse
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
