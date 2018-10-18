@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            <img src="{{ asset('images/logo_caronae_with_text.png') }}" class="logo-img" alt="{{ config('app.name') }}">
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. Esta é uma mensagem automática. Caso queira falar conosco, envie um e-mail para <a href="mailto:caronae@fundoverde.ufrj.br">caronae@fundoverde.ufrj.br</a>.
        @endcomponent
    @endslot
@endcomponent
