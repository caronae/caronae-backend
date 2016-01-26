@extends('templates.ranking')

@section('title')
Motoristas melhor avaliados
@endsection

@section('table')
    <table class="table">
        <thead>
        <tr>
            <th>Nome</th>
            <th>Perfil UFRJ</th>
            <th>Curso</th>
            <th>Caronas Dadas</th>
            <th>Caronistas levados</th>
            <th width="66px">Feedback Positivo</th>
            <th width="66px">Feedback Negativo</th>
            <th width="66px">Sem Feedback</th>
            <th width="80px">Reputação</th>
        </tr>
        </thead>
    </table>
@endsection

@section('js')
@parent
<script>
    $(function() {

        $('.table').DataTable({
            columns: [
                {data: 'name'},
                {data: 'profile'},
                {data: 'course'},
                {data: 'caronas'},
                {data: 'caronistas'},
                {   data: 'feedback_positivo',
                    render: function ( data, type, full, meta ) {
                        return '<p class="alert alert-success text-center">'+data+'</p>';
                    }
                },
                {   data: 'feedback_negativo',
                    render: function ( data, type, full, meta ) {
                        return '<p class="alert alert-danger text-center">'+data+'</p>';
                    }
                },
                {   data: 'sem_feedback',
                    render: function ( data, type, full, meta ) {
                        return '<p class="alert alert-warning text-center">'+data+'</p>';
                    }
                },
                {   data: 'reputacao',
                    render: function ( data, type, full, meta ) {
                        var percentage = Math.round(data*100) + '%';
                        return '<p class="alert alert-info text-center"><strong>'+percentage+'</strong></p>';
                    }
                }
            ]
        });
    });
</script>
@endsection