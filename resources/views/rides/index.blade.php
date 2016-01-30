@extends('templates.ranking')

@section('title')
    Caronas dadas
@endsection

@section('table')
    <table class="table">
        <thead>
        <tr>
            <th>Motorista</th>
            <th>Data</th>
            <th>Hora</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Distancia</th>
        </tr>
        </thead>
    </table>
@endsection

@section('js')
    @parent
    <script>
        $(function() {

            var formatDate = function(date){
                var parts = date.split('-');
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            };

            var formatTime = function(time){
                return time.slice(0, -3);
            };

            $('.table').DataTable({
                columns: [
                    {data: 'driver'},
                    {
                        render: function ( data, type, full, meta ) {
                            return formatDate(full.mydate);
                        }
                    },
                    {
                        render: function ( data, type, full, meta ) {
                            return formatTime(full.mytime);
                        }
                    },
                    {
                        render: function ( data, type, full, meta ) {
                            if(full.going)
                                return  full.neighborhood + '/' + full.myzone;
                            else
                                return 'Fundão';
                        }
                    },
                    {
                        render: function ( data, type, full, meta ) {
                            if(full.going)
                                return 'Fundão';
                            else
                                return  full.neighborhood + '/' + full.myzone;
                        }
                    },
                    {   data: 'distance',
                        render: function ( data, type, full, meta ) {
                            return Math.round(data * 10) / 10 + ' Km';
                        }
                    }
                ]
            });
        });
    </script>
@endsection