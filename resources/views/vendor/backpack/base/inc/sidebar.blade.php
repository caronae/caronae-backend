  @if (Auth::check())
    <aside class="main-sidebar">
      <section class="sidebar">
        <div class="user-panel">
          <div class="info">
          <p>{{ Auth::user()->name }}</p>
          <small>{{ Auth::user()->email }}</small>
          </div>
        </div>

        <ul class="sidebar-menu">
          <li class="header">{{ trans('backpack::base.administration') }}</li>

          <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> <span>Painel</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'admin').'/users') }}"><i class="fa fa-user"></i> <span>Usuários</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'admin').'/rides') }}"><i class="fa fa-car"></i> <span>Caronas</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'admin').'/institutions') }}"><i class="fa fa-university"></i> <span>Instituições</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'admin').'/hubs') }}"><i class="fa fa-map-marker"></i> <span>Hubs</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'admin').'/zones') }}"><i class="fa fa-location-arrow"></i> <span>Zonas e bairros</span></a></li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'admin').'/admins') }}"><i class="fa fa-lock"></i> <span>Administradores</span></a></li>
          <li><a href="{{ route('logs') }}"><i class="fa fa-terminal"></i> <span>Logs</span></a></li>

          <li class="header">{{ trans('backpack::base.user') }}</li>
          <li><a href="{{ url(config('backpack.base.route_prefix', 'admin').'/logout') }}"><i class="fa fa-sign-out"></i> <span>{{ trans('backpack::base.logout') }}</span></a></li>
        </ul>
      </section>
    </aside>
@endif
