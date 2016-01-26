@extends('templates.ranking')

@section('title')
Motoristas com maior ocupação média
@endsection

@section('table')
<table class="table">
    <thead>
    <tr>
        <th>Nome</th>
        <th>Perfil UFRJ</th>
        <th>Curso</th>
        <th width="66px">Caronas</th>
        <th width="66px">Moda</th>
        <th width="80px">Média</th>
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
                {data: 'moda'},
                {
                    data: 'media',
                    render: function (data, type, full, meta) {
                        var trucated = Math.round(data*100)/100;
                        return '<div class="alert alert-info text-center"><strong>' + trucated + '</strong></div>';
                    }
                }
            ]
        })

    });
</script>
@endsection