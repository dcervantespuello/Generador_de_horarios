function agregar(nombre, creditos) {

    // Mensaje debajo de la tabla
    var fila_cero = document.getElementById('fila_cero');

    // Tomando todo el contenido de la tabla
    var contenido = [];

    var filas = document.getElementById('seleccionados').children[1].children;

    for (var i = 0; i < filas.length; i++) {
        contenido[i] = [filas[i].children[0].textContent.trim(), filas[i].children[1].textContent.trim()];
    }

    // Saber si el curso a agregar ya está en la tabla
    var encontrado = false;

    for (var j = 0; j < contenido.length; j++) {
        if (contenido[j][0] == nombre) {
            encontrado = true;
        }
    }

    if (!encontrado) {
        // Quitamos el mensaje de error
        document.getElementById('lista').innerHTML = '';

        // Cantidad de filas actual en la tabla
        var cantidad = document.getElementById('seleccionados').children[1].children.length;

        // Si la cantidad era cero...
        if (cantidad == 0) {
            // Quita el parrafo si el número de filas en la tabla de seleccionados era cero
            fila_cero.innerHTML = "";

            // Mostrar el botón para generar horario
            document.getElementById('enviar').style.display = 'block';

            // Mostramos el pie de la tabla
            document.getElementById('pie').style.display = 'table-footer-group';
        }

        // Obtenemos la suma de todos los créditos
        var suma = 0;

        for (var x = 0; x < contenido.length; x++) {
            suma = suma + parseInt(contenido[x][1]);
        }

        suma = suma + parseInt(creditos);

        // Hacemos que la suma de los créditos no se pase de 18.
        if (suma <= 18) {

            var pie = document.getElementById('seleccionados').children[2].children[0].children[1];
            pie.innerHTML = suma;

            // Se agrega la fila
            contenido.push([nombre, creditos]);

            // Se limpia la tabla
            document.getElementById("seleccionados").children[1].innerHTML = "";

            // Se coloca la tabla a la derecha
            for (var k = 0; k < contenido.length; k++) {
                document.getElementById("seleccionados").children[1].insertRow(-1).innerHTML =
                    '<tr><td>' + contenido[k][0] + '</td><td>' + contenido[k][1] +
                    '</td><td><input class="btn btn-danger btn-quitar" type="button" value="Quitar" onclick = \'quitar("' +
                    contenido[k][0] + '", "' + contenido[k][1] + '")\'></td></tr>';
            }

        } else {
            document.getElementById('lista').innerHTML = '<li style="text-align:center; color:red;" class="py-3">No puede pasarse de 18 créditos.</li>';
        }

    } else {
        document.getElementById('lista').innerHTML = '<li style="text-align:center; color:red;" class="py-3">' + nombre + ' ya fue agregado.</li>';
    }

}

function quitar(nombre, creditos) {

    // Tomando todo el contenido de la tabla
    var contenido = [];

    var filas = document.getElementById('seleccionados').children[1].children;

    for (var i = 0; i < filas.length; i++) {
        contenido[i] = [filas[i].children[0].textContent.trim(), filas[i].children[1].textContent.trim()];
    }

    // Posición del elemento a eliminar
    var posicion = 0;

    for (var j = 0; j < contenido.length; j++) {
        if (contenido[j][0] == nombre) {
            posicion = j;
        }
    }

    // Se elimina la fila
    contenido.splice(posicion, 1);

    // Se limpia la tabla
    document.getElementById("seleccionados").children[1].innerHTML = "";

    // Se coloca la tabla a la derecha
    for (var k = 0; k < contenido.length; k++) {
        document.getElementById("seleccionados").children[1].insertRow(-1).innerHTML =
            '<tr><td>' + contenido[k][0] + '</td><td>' + contenido[k][1] +
            '</td><td><input class="btn btn-danger btn-quitar" type="button" value="Quitar" onclick = \'quitar("' +
            contenido[k][0] + '", "' + contenido[k][1] + '")\'></td></tr>';
    }

    // Obtenemos la suma de todos los créditos
    var suma = 0;

    for (var x = 0; x < contenido.length; x++) {
        suma = suma + parseInt(contenido[x][1]);
    }

    // Agregamos la suma al pie de la tabla
    var pie = document.getElementById('seleccionados').children[2].children[0].children[1];
    pie.innerHTML = suma;

    // Cantidad de filas actual en la tabla
    var cantidad = document.getElementById('seleccionados').children[1].children.length;

    // Si la cantidad de filas llega a cero...
    if (cantidad == 0) {
        // Quita el párrafo debajo de la tabla
        document.getElementById('fila_cero').innerHTML = "¡No hay cursos seleccionados!";

        // Ocultamos el botón de generar horario
        document.getElementById('enviar').style.display = 'none';

        // Ocultamos el pie de la tabla
        document.getElementById('pie').style.display = 'none';
    }

}

// Función del buscador
$(document).ready(function() {
    $("#buscador").keyup(function() {
        _this = this;
        // Mostrar solamente los TR correctos y ocultar el resto
        $.each($("#cursos tbody tr"), function() {
            if ($(this).text().toLowerCase().indexOf($(_this).val().toLowerCase()) === -1)
                $(this).hide();
            else
                $(this).show();
        });
    });
});

function enviar() {

    // Tomando todo el contenido de la tabla de la lista de cursos seleccionados
    var contenido = [];

    var filas = document.getElementById('seleccionados').children[1].children;

    for (var i = 0; i < filas.length; i++) {
        contenido[i] = filas[i].children[0].textContent.trim();
    }

    $.ajax({
        url: "/hill_climbing",
        type: 'post',
        timeout: 10000,
        dataType: 'json',
        data: {
            'contenido': JSON.stringify(contenido)
        },
        success: function(response) {
            document.getElementById('enviar').innerHTML = 'OTRA COSA';
            console.log(response.nombres);
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