@extends('templates.admin')

@section('conteudo')
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="title">
                @yield('title')
            </span>

            <div class="btn-group export-dropdown">
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

            @include('includes.period-search-form', [
                'formClass' => 'search-period-form',
                'defaultStart' => \Carbon\Carbon::now()->subMonth(),
                'defaultEnd' => \Carbon\Carbon::now()
            ])

        </div>

        <div class="error-alert alert alert-danger alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <span class="content"></span>
        </div>

        @yield('table')

        <!-- Modal usada em rides/index.blade.php -->
        <div class="modal-riders modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Caronistas</h4>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Ok</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </div>

@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/ranking.js') }}"></script>
@endsection
