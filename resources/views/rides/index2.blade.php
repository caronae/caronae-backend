@extends('backpack::layout')

@section('header')
    <section class="content-header">
      <h1>
        Caronas <small>Todas as caronas concluídas no período selecionado</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url(config('backpack.base.route_prefix', 'admin')) }}">{{ config('backpack.base.project_name') }}</a></li>
        <li class="active">Caronas</li>
      </ol>
    </section>
@endsection


@section('content')
  <div class="row">

    <div class="col-md-12">
      <div class="box">
        <div class="box-header with-border">
            @include('includes.period-search-form', [
                'formClass' => 'search-period-form',
                'defaultStart' => \Carbon\Carbon::now()->subMonth(),
                'defaultEnd' => \Carbon\Carbon::now()
            ])
                    
          <div id="datatable_button_stack" class="pull-right text-right"></div>
        </div>

        <div class="box-body table-responsive">

        <table class="table table-bordered table-striped display">
            <thead>
              <tr>
                <th>Motorista</th>
                <th>Curso</th>
                <th style="width: 70px">Data</th>
                <th style="width: 30px">Hora</th>
                <th>Origem</th>
                <th>Destino</th>
                <th style="width: 60px">Distância</th>
                <th style="width: 60px">Distância Total</th>
                <th style="width: 60px">Total de Caronas</th>
                <th style="width: 60px">Distância Média</th>
                <th style="width: 60px">Ações</th>
              </tr>
            </thead>
            <tbody>

            </tbody>
          </table>
        </div><!-- /.box-body -->

      </div><!-- /.box -->
    </div>

  </div>
@endsection

@section('after_styles')
  <!-- DATA TABLES -->
  <link href="{{ asset('vendor/adminlte/plugins/datatables/dataTables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/crud.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/form.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/list.css') }}">
@endsection

@section('after_scripts')
    <!-- DATA TABLES SCRIPT -->
    <script src="{{ asset('vendor/adminlte/plugins/datatables/jquery.dataTables.js') }}" type="text/javascript"></script>

    <script src="{{ asset('vendor/backpack/crud/js/crud.js') }}"></script>
    <script src="{{ asset('vendor/backpack/crud/js/form.js') }}"></script>
    <script src="{{ asset('vendor/backpack/crud/js/list.js') }}"></script>

    <script src="{{ asset('vendor/adminlte/plugins/datatables/dataTables.bootstrap.js') }}" type="text/javascript"></script>
    @parent

    <script src="{{ asset('vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/routes.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/ranking.js') }}"></script>
    <script>
        $(function() {
            var formatDate = function(date){
                var parts = date.split('-');
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            };

            var formatTime = function(time){
                return time.slice(0, -3);
            };

            var formatDistance = function ( data, type, full, meta ) {
                if(data === null) return '<span title="Zona e/ou bairro com distância desconhecida.">?</span>';
                return Math.round(data * 10) / 10 + ' Km';
            };

            $('.table').DataTable({
                createdRow: function( row, data ) {
                    $(row).addClass( 'carona' );
                    $(row).attr('data-ride-id', data.id);
                },
                columns: [
                    {data: 'driver'},
                    {data: 'course'},
                    { // data
                        render: function ( data, type, full, meta ) {
                            return formatDate(full.mydate);
                        }
                    },
                    { // hora
                        render: function ( data, type, full, meta ) {
                            return formatTime(full.mytime);
                        }
                    },
                    { // origem
                        render: function ( data, type, full, meta ) {
                            if(full.going)
                                return  full.neighborhood + '/' + full.myzone;
                            else
                                return 'Fundão/'+full.hub;
                        }
                    },
                    { // destino
                        render: function ( data, type, full, meta ) {
                            if(full.going)
                                return 'Fundão/'+full.hub;
                            else
                                return  full.neighborhood + '/' + full.myzone;
                        }
                    },
                    {
                        data: 'distance',
                        render: formatDistance
                    },
                    {
                        data: 'distancia_total',
                        className: 'start-of-driver-data',
                        render: formatDistance
                    },
                    {
                        data: 'numero_de_caronas'
                    },
                    {
                        data: 'distancia_media',
                        render: formatDistance
                    },
                    {
                        render: function ( data, type, full, meta ) {
                            return '<a href="'+routes.ride(full.id)+'" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> Ver</a>'
                        }
                    },
                ]
            });
        });
    </script>
@endsection

