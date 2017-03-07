@extends('templates.admin')

@section('conteudo')
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="title">
                Usuários
            </span>

            @if($banned)
                <?php $getParamsToButton = ['banned' => true] ?>
                <?php $getParamsToExport = [] ?>
            @else
                <?php $getParamsToButton = [] ?>
                <?php $getParamsToExport = ['banned' => true] ?>
            @endif

            <div class="btn-group export-dropdown">
                <a href="{{ action('Admin\UserController@indexExcel', $getParamsToExport + ['type' => 'xlsx']) }}" class="btn btn-success" title="Exportar para .xlsx">
                    <span class="glyphicon glyphicon-list-alt"></span>
                    <span class="glyphicon glyphicon-new-window"></span>
                </a>
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="{{ action('Admin\UserController@indexExcel', $getParamsToExport + ['type' => 'xlsx']) }}">Exportar para .xlsx</a></li>
                    <li><a href="{{ action('Admin\UserController@indexExcel', $getParamsToExport + ['type' => 'csv']) }}">Exportar para .csv</a></li>
                </ul>
            </div>

            <a class="btn btn-primary pull-right" href="{{ action('Admin\UserController@index', $getParamsToButton) }}">
                <span class="glyphicon glyphicon-ban-circle"></span>
                Ver usuários banidos
            </a>
        </div>

        @include('includes.message')

        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Perfil UFRJ</th>
                    <th>Email</th>
                    <th>Curso</th>
                    <th>Bairro</th>
                    <th style="width: 73px">Ações</th>
                    <!-- Um nova coluna precisa ter seu td adicionado aqui -->
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
                'url': getQueryParameterByName('banned') ? routes.users.banned : routes.users.active,
                'dataSrc': ''
            },
            columns: [
                /*
                 * Para cada coluna, é preciso um objeto descrevendo ela.
                 * Para só mostrar o valor, basta usar:
                 * { data: 'valor' },
                 *
                 * Para casos mais complexos, siga os exemplos abaixo ou leia a documentação
                 * do Datatables. Ex: https://datatables.net/reference/option/columns
                 */
                {data: 'name'},
                {data: 'profile'},
                {
                    data: 'email',
                    render: function(data, type, full, meta) {
                        return $('<a>')
                                .attr('href', 'mailto:'+data)
                                .attr('target', '_blank')
                                .text(data)
                               .prop('outerHTML');
                    }
                },
                {data: 'course'},
                {data: 'location'},
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function ( data, type, full, meta ) {
                        var action, message, buttonLabel, glyphicon, btnType;
                        if(getQueryParameterByName('banned')){
                            action = routes.users.unban(data);
                            message = "Deseja mesmo desbanir esse usuario?";
                            buttonLabel = 'Desbanir';
                            glyphicon = "ok-circle";
                            btnType = "success";
                        } else {
                            action = routes.users.banish(data);
                            message = "Deseja mesmo banir esse usuario?";
                            buttonLabel = 'Banir';
                            glyphicon = "ban-circle";
                            btnType= "warning";
                        }
                        return $('<form>')
                                .attr('action', action)
                                .attr('method', 'post')
                                .attr('onsubmit', 'return confirm(\''+message+'\')')
                                .append(
                                    $('<input>')
                                    .attr('type', 'hidden')
                                    .attr('name', '_token')
                                    .attr('value', csrf_token())
                                )
                                .append(
                                    $('<button>')
                                    .attr('type', 'submit')
                                    .attr('class', 'btn btn-'+btnType)
                                    .append(
                                        $('<span>')
                                        .attr('class', 'glyphicon glyphicon-'+glyphicon)
                                    )
                                    .append(buttonLabel)
                                ).prop('outerHTML');
                    }
                }
            ]
        });
    });
</script>
@endsection