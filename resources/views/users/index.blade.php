@extends('templates.admin')

@section('conteudo')
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="title">
                Usuários
            </span>
            @if($banned)
                <a class="btn btn-primary pull-right" href="{{ action('UserController@index', ['banned' => 'true']) }}">
                    <span class="glyphicon glyphicon-ban-circle"></span>
                    Ver usuários banidos
                </a>
            @else
                <a class="btn btn-primary pull-right" href="{{ action('UserController@index') }}">
                    <span class="glyphicon glyphicon-ok-circle"></span>
                    Ver usuários ativos
                </a>
            @endif
        </div>

        @include('includes.message')

        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Perfil UFRJ</th>
                    <th>Curso</th>
                    <th>Bairro</th>
                    <th style="width: 73px">Ações</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('js')
<script>
    $(function() {

        $('.table').DataTable({
            "ajax" : {
                'url': url('admin/usuarios.json') + (getQueryParameterByName('banned') ? '?banned=true' : ''),
                'dataSrc': ''
            },
            columns: [
                {data: 'name'},
                {data: 'profile'},
                {data: 'course'},
                {data: 'location'},
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function ( data, type, full, meta ) {
                        var action, message, buttonLabel, glyphicon, btnType;
                        if(getQueryParameterByName('banned')){
                            action = url('admin/usuario/'+data+'/desbanir');
                            message = "Deseja mesmo desbanir esse usuario?";
                            buttonLabel = 'Desbanir';
                            glyphicon = "ok-circle";
                            btnType = "success";
                        } else {
                            action = url('admin/usuario/'+data+'/banir');
                            message = "Deseja mesmo banir esse usuario?";
                            buttonLabel = 'Banir';
                            glyphicon = "ban-circle";
                            btnType= "warning";
                        }
                        return $('<div>' +
                                '<form action="'+action+'" method="post" onsubmit="return confirm(\''+message+'\')">'+
                                '<input type="hidden" name="_token" value="'+csrf_token()+'"/>'+
                                '<button type="submit" class="btn btn-'+btnType+'">'+
                                '<span class="glyphicon glyphicon-'+glyphicon+'"></span>' +
                                buttonLabel+
                                '</button>'+
                                '</form>'+
                                '</div>')
                                .html()
                    }
                }
            ]
        });
    });
</script>
@endsection