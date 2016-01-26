@extends('templates.admin')

@section('conteudo')
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="title">
                @yield('title')
            </span>

            <form class="form-inline pull-right search-period-form">
                <span>De:</span>
                <input
                        type="text"
                        class="form-control period-start"
                        value="{{ \Carbon\Carbon::now()->subMonth()->format('d/m/Y') }}"
                        placeholder="esse dia"
                        data-provide="datepicker"
                >
                <span>Até:</span>
                <input
                        type="text"
                        class="form-control period-end"
                        value="{{ \Carbon\Carbon::now()->format('d/m/Y') }}"
                        placeholder="esse dia"
                        data-provide="datepicker"
                >

                <button class="btn btn-primary">
                    <span class="glyphicon glyphicon-search"></span>
                    Mostrar
                </button>

                <div class="btn-group">
                    <button type="button" class="btn btn-success export-button" title="Exportar para .xlsx">
                        <span class="glyphicon glyphicon-list-alt"></span>
                        <span class="glyphicon glyphicon-new-window"></span>
                    </button>
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="export-button" href="javascript:void(0)">Exportar para .xlsx</a></li>
                        <li><a class="export-button-csv" href="javascript:void(0)">Exportar para .csv</a></li>
                    </ul>
                </div>
            </form>

        </div>

        <div class="error-alert alert alert-danger alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <span class="content"></span>
        </div>

        @yield('table')

    </div>

@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/ranking.js') }}"></script>
@endsection
