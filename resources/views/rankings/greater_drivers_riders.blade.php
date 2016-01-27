@extends('templates.ranking')

@section('title')
Motoristas com mais caronas
@endsection

@section('table')
<table class="table">
    <thead>
    <tr>
        <th>Nome</th>
        <th>Perfil UFRJ</th>
        <th>Curso</th>
        <th width="66px">Carbono Economizado</th>
        <th width="80px">Caronas</th>
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
                {
                    data: 'carbono_economizado',
                    render: function (data, type, full, meta) {
                        return '<div class="alert alert-success text-center"><strong>' + data + '</strong></div>';
                    }
                },
                {
                    data: 'caronas',
                    render: function (data, type, full, meta) {
                        return '<div class="alert alert-info text-center"><strong>' + data + '</strong></div>';
                    }
                }
            ]
        })

    });
</script>
@endsection