@extends('templates.ranking')

@section('title')
Caronistas com mais caronas
@endsection

@section('table')
<table class="table">
    <thead>
    <tr>
        <th>Nome</th>
        <th>Perfil UFRJ</th>
        <th>Curso</th>
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