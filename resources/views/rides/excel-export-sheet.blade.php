<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Document</title>
</head>
<body>

<table class="table">
    <thead>
        <tr>
            <th>Motorista</th>
            <th>Curso</th>
            <th>Data</th>
            <th>Hora</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Distancia</th>
            <th>Distancia Total</th>
            <th>Total de Caronas</th>
            <th>Distancia Média</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td>
                    {{ $row['driver'] }}
                </td>
                <td>
                    {{ $row['course'] }}
                </td>
                <td>
                    {{ $row['mydate'] }}
                </td>
                <td>
                    {{ $row['mytime'] }}
                </td>
                <td>
                    {{ $row['going'] ? $row['neighborhood'] . '/' . $row['myzone'] : "Fundão/".$row['hub'] }}
                </td>
                <td>
                    {{ $row['going'] ? "Fundão/".$row['hub'] : $row['neighborhood'] . '/' . $row['myzone'] }}
                </td>
                <td>
                    {{ $row['distance'] }}
                </td>
                <td>
                    {{ $row['distancia_total'] }}
                </td>
                <td>
                    {{ $row['numero_de_caronas'] }}
                </td>
                <td>
                    {{ $row['distancia_media'] }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
