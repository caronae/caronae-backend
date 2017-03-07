@extends('templates.ranking')

@section('title')
    Caronas dadas
@endsection

@section('table')
    <table class="table table-hover">
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

            var formatDistance = function ( data, type, full, meta ) {
                if(data === null) return '<span title="Zona e/ou bairro com distância desconhecida.">?</span>';
                return Math.round(data * 10) / 10 + ' Km';
            };

            var showModal = function(riders){
                var $modal = $('.modal-riders');

                var $modalBody = $modal.find('.modal-body');
                $modalBody.html('');
                for(var i=0; i < riders.length; i++){
                    var rider = riders[i];
                    $modalBody
                    .append(
                        $('<p>')
                        .append(rider.name)
                        .append(' (')
                        .append(
                            $('<a>')
                            .attr('href', "mailto:"+rider.email)
                            .attr('target', '_blank')
                            .append(rider.email)
                        )
                        .append(')')
                    );
                }

                $modal.modal();
            };

            $('table').on('click', 'tr.carona', function(){
                var $this = $(this);
                var rideId = $this.data('ride-id');

                $this.addClass('loading');
                $.get(routes.riders(rideId)).then(function(riders){
                    showModal(riders);
                    $this.removeClass('loading');
                });
            });

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
                    }
                ]
            });
        });
    </script>
@endsection
