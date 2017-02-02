<div class="user-details">
	<img src="{{ $u->profile_pic_url or asset('images/user-placeholder.png') }}" class="image">

	<ul class="properties">
		<li class="property"><span class="column-name">Nome</span> {{ $u->name }}</li>
		<li class="property">
			<span class="column-name">E-mail</span> 
			@if ($u->email)
			<a href="mailto:{{$u->email}}">{{$u->email}}</a>
			@endif
		</li>
		<li class="property"><span class="column-name">Telefone</span> {{ $u->phone_number }}</li>
		<li class="property"><span class="column-name">Perfil</span> {{ $u->profile }}</li>
		<li class="property"><span class="column-name">Curso</span> {{ $u->course }}</li>
		<li class="property"><span class="column-name">Bairro</span> {{ $u->location }}</li>
		<li class="property"><span class="column-name">Motorista</span> {{ $u->car_owner ? 'Sim' : 'Não' }}</li>
		@if ($u->car_owner)
			<li class="property"><span class="column-name">Modelo do carro</span> {{ $u->car_model }}</li>
			<li class="property"><span class="column-name">Placa do carro</span> {{ $u->car_plate }}</li>
			<li class="property"><span class="column-name">Cor do carro</span> {{ $u->car_color }}</li>
		@endif
	</ul>
</div>