@extends('templates.admin')

@section('conteudo')
    <form action="{{action('AdminController@postEdit')}}" method='post'>
        {!! csrf_field() !!}
        <div class="panel panel-default">
            <div class="panel-heading">
                <a class="btn" href="{{ back()->getTargetUrl() }}">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
                <span class="title">
                    Editar Administrador
                </span>
            </div>
            <div class="panel-body">
                @include('includes.errors')
                @include('includes.message')

                <?php $model = $admin; ?>
                <div class="form-group">
                    <?php $field = 'name' ?>
                    <label for="{{$field}}">Nome</label>
                    <input type="text" class="form-control" id="{{$field}}" name="{{$field}}" value="{{old($field, $model->$field)}}" placeholder="{{"Nome aqui..."}}">
                </div>

                <div class="form-group">
                    <?php $field = 'email' ?>
                    <label for="{{$field}}">{{ucfirst($field)}}</label>
                    <input type="text" class="form-control" id="{{$field}}" name="{{$field}}" value="{{old($field, $model->$field)}}" placeholder="{{"$field aqui..."}}">
                </div>

                <p class="text-info">Digite uma nova senha abaixo somente se quiser mudar de senha</p>
                <div class="form-group">
                    <?php $field = 'password' ?>
                    <label for="{{$field}}">Senha</label>
                    <input type="text" class="form-control" id="{{$field}}" name="{{$field}}" value="" placeholder="Senha aqui...">
                </div>

                <div class="form-group">
                    <?php $field = 'password_confirmation' ?>
                    <label for="{{$field}}">Confirmar senha</label>
                    <input type="text" class="form-control" id="{{$field}}" name="{{$field}}" value="" placeholder="Digite a mesma senha de antes aqui...">
                </div>

            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-success">
                    <span class="glyphicon glyphicon-ok"></span>
                    Salvar
                </button>
            </div>
        </div>
    </form>
@endsection
