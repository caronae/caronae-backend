<div class="model-details user-details">
    <div class="user-image">
        <img src="{{ $entry->profile_pic_url or asset('images/user-placeholder.png') }}">
    </div>

    <ul class="properties">
        <li class="property"><span class="column-name">Nome</span> {{ $entry->name }}</li>
        <li class="property">
            <span class="column-name">Usuário desde</span>
            {{ ucfirst(Date::parse($entry->created_at)->format('F')) }}
            de
            {{ $entry->created_at->format('Y') }}
            <small>
                ({{ $entry->created_at->diffForHumans() }})
            </small>
        </li>
        <li class="property">
            <span class="column-name">E-mail</span>
            @if ($entry->email)
                <a href="mailto:{{$entry->email}}">{{$entry->email}}</a>
            @endif
        </li>
        <li class="property"><span class="column-name">Telefone</span> {{ $entry->phone_number }}</li>
        <li class="property"><span class="column-name">Perfil</span> {{ $entry->profile }}</li>
        <li class="property"><span class="column-name">Curso</span> {{ $entry->course }}</li>
        <li class="property"><span class="column-name">Bairro</span> {{ $entry->location }}</li>
        <li class="property"><span
                    class="column-name">Motorista</span> {{ $entry->car_owner ? 'Sim' : 'Não' }}</li>
        @if ($entry->car_owner)
            <li class="property"><span class="column-name">Modelo do carro</span> {{ $entry->car_model }}
            </li>
            <li class="property"><span class="column-name">Placa do carro</span> {{ $entry->car_plate }}
            </li>
            <li class="property"><span class="column-name">Cor do carro</span> {{ $entry->car_color }}</li>
        @endif
    </ul>
</div>