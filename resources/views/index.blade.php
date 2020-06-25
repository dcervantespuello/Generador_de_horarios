@extends('layouts.app')

@section('contenido')

<div class="container-fluid">
    <div class="row">
        <div class="col-7">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre del curso</th>
                        <th>Créditos</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cursos as $nombre => $curso)

                    <!-- Acortando textos -->
                    @php
                    $creditos = $curso['creditos'];
                    @endphp

                    <tr>
                        <th>{{ $nombre }}</th>
                        <td>{{ $curso['creditos'] }}</td>
                        <td><input class="btn btn-success btn-agregar" type="button" value="Agregar"
                                onclick='agregar("{{ $nombre }}", "{{ $creditos }}")'></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-5">
            <h3>Cursos seleccionados</h3>
            <table class="table table-hover" id='seleccionados'>
                <thead>
                    <tr>
                        <th>Nombre de curso</th>
                        <th>Créditos</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <p id="fila_cero">¡No hay cursos seleccionados!</p>
        </div>
    </div>
</div>

@endsection


@section('scripts')

<script type="text/javascript">
// $(document).ready(function() {

// });

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function agregar(nombre, creditos) {

    var cuerpo = document.getElementById('seleccionados').children[1];
    var cantidad = cuerpo.children.length;

    // Tomando todo el contenido de la tabla de la lista de cursos seleccionados
    var contenido = [];

    var filas = document.getElementById('seleccionados').children[1].children;

    for (var i = 0; i < filas.length; i++) {
        contenido[i] = [filas[i].children[0].textContent.trim(), filas[i].children[1].textContent.trim()];
    }


    $.ajax({
        url: "{{ route('agregar') }}",
        dataType: 'json',
        data: {
            'nombre': nombre,
            'creditos': creditos,
            'cantidad': cantidad,
            'contenido': JSON.stringify(contenido)
        },
        type: 'post',
        success: function(response) {
            console.log(response);
            var cantidad = response.cantidad;

            if (cantidad == 0) {
                document.getElementById('fila_cero').innerHTML = "";
            }

            document.getElementById("seleccionados").children[1].insertRow(-1).innerHTML =
                '<tr><td>' + response.nombre + '</td><td>' + response.creditos +
                '</td><td><input class="btn btn-danger btn-quitar" type="button" value="Quitar" onclick = \'quitar("' +
                response.nombre + '", "' + response.creditos + '")\'></td></tr>';
        },
        statusCode: {
            404: function() {
                alert('web not found');
            }
        },
        error: function(x, xs, xt) {
            window.open(JSON.stringify(x));
            //alert('error: ' + JSON.stringify(x) +"\n error string: "+ xs + "\n error throwed: " + xt);
        }
    });

}

function quitar(nombre, creditos) {

    var cuerpo = document.getElementById('seleccionados').children[1];
    var cantidad = cuerpo.children.length;

    $.ajax({
        url: "{{ route('quitar') }}",
        dataType: 'json',
        data: {
            'nombre': nombre,
            'creditos': creditos,
            'cantidad': cantidad
        },
        type: 'post',
        success: function(response) {
            var cantidad = response.cantidad;

            document.getElementById("seleccionados").children[1].deleteRow();

            if (cantidad == 0) {
                document.getElementById('fila_cero').innerHTML = "";
            }
        },
        statusCode: {
            404: function() {
                alert('web not found');
            }
        },
        error: function(x, xs, xt) {
            window.open(JSON.stringify(x));
            //alert('error: ' + JSON.stringify(x) +"\n error string: "+ xs + "\n error throwed: " + xt);
        }
    });

}
</script>

@endsection