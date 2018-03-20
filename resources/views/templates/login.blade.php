@extends('templates.base')

@section('body')
<div class="top-content">
    <div class="inner-bg">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12 logo">
                    <img src="{{ asset('images/logo_caronae_with_text.png') }}" class="logo-img">
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 col-sm-offset-3 form-box">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection