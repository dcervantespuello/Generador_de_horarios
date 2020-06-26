<!doctype html>
<html lang="es">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <!-- Normalize CSS -->
    <link rel="stylesheet" href="{{ asset('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/estilo.css') }}">

    <title>HORARIOS UTB</title>
</head>

<body>

    @yield('contenido')

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"
        integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="{{ asset('js/logica.js') }}"></script>
    
    <!-- <script>
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
    </script> -->

</body>

</html>