<table class="table table-striped table-hover display responsive nowrap dataTable dtr-inline">
    <thead>
    <th>
        Título
    </th>
    <th>
        Motorista
    </th>
    <th>
        Situação
    </th>
    <th>
        Concluída?
    </th>
    <th>
        Caronistas
    </th>
    <th>
        Ações
    </th>
    </thead>
    <tbody>
    @foreach($entry as $ride)
        <tr>
            <td>
                {{$ride->title}}
            </td>
            <td>
                {{$ride->driver()->name}}
            </td>
            <td>
                @switch($ride->pivot->status)
                    @case('driver')
                    Motorista
                    @break
                    @case('accepted')
                    Aceita
                    @break
                    @case('refused')
                    Recusada
                    @break
                    @case('pending')
                    Pendente
                    @break
                @endswitch
            </td>
            <td class="text-center">
                {{$ride->done ? 'Sim' : 'Não'}}
            </td>
            <td class="text-center">
                {{$ride->riders->count()}}
            </td>
            <td class="text-center">
                <a href="{{url("/admin/rides/{$ride->id}")}}" class="btn btn-xs btn-default">
                    <i class="fa fa-eye"></i> Visualizar
                </a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>