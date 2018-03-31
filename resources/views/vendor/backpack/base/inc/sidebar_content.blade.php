<li><a href="{{ backpack_url('dashboard') }}"><i class="fa fa-dashboard"></i> <span>Painel</span></a></li>
<li><a href="{{ backpack_url('users') }}"><i class="fa fa-user"></i> <span>Usuários</span></a></li>
<li><a href="{{ backpack_url('rides') }}"><i class="fa fa-car"></i> <span>Caronas</span></a></li>
<li><a href="{{ backpack_url('institutions') }}"><i class="fa fa-university"></i> <span>Instituições</span></a></li>
<li><a href="{{ backpack_url('hubs') }}"><i class="fa fa-map-marker"></i> <span>Hubs</span></a></li>
<li><a href="{{ backpack_url('zones') }}"><i class="fa fa-location-arrow"></i> <span>Zonas e bairros</span></a></li>
<li><a href="{{ backpack_url('admins') }}"><i class="fa fa-lock"></i> <span>Administradores</span></a></li>

<li class="header">{{ trans('backpack::base.user') }}</li>
<li><a href="{{ url(config('backpack.base.route_prefix', 'admin').'/logout') }}"><i class="fa fa-sign-out"></i> <span>{{ trans('backpack::base.logout') }}</span></a></li>
