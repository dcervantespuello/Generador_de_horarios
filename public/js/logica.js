function agregar(nombre, creditos) {

    // Mensaje debajo de la tabla
    var fila_cero = document.getElementById('fila_cero');

    // Tomando todo el contenido de la tabla
    var contenido = [];

    var filas = document.getElementById('seleccionados').children[1].children;

    for (var i = 0; i < filas.length; i++) {
        contenido[i] = [filas[i].children[0].children[0].value.trim(), filas[i].children[1].textContent.trim()];
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
        document.getElementById('errores').style.display = 'none';
        document.getElementById('lista').innerHTML = '';

        // Cantidad de filas actual en la tabla
        var cantidad = document.getElementById('seleccionados').children[1].children.length;

        // Si la cantidad era cero...
        if (cantidad == 0) {
            // Quita el parrafo si el número de filas en la tabla de seleccionados era cero
            fila_cero.style.display = 'none';

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
                document.getElementById("seleccionados").children[1].insertRow(-1).innerHTML = '<tr><td><input type="text" name="nombres[]" readonly class="form-control-plaintext nombre" value="' + contenido[k][0] + '"></td><td>' + contenido[k][1] + '</td><td><input class="btn btn-danger btn-quitar" type="button" value="Quitar" onclick = \'quitar("' + contenido[k][0] + '", "' + contenido[k][1] + '")\'></td></tr>';
            }

        } else {
            document.getElementById('errores').style.display = 'block';
            document.getElementById('lista').innerHTML = 'No puede pasarse de 18 créditos';
        }

    } else {
        document.getElementById('errores').style.display = 'block';
        document.getElementById('lista').innerHTML = nombre + ' ya fue agregado';
    }

}

function quitar(nombre, creditos) {

    // Tomando todo el contenido de la tabla
    var contenido = [];

    var filas = document.getElementById('seleccionados').children[1].children;

    for (var i = 0; i < filas.length; i++) {
        contenido[i] = [filas[i].children[0].children[0].value.trim(), filas[i].children[1].textContent.trim()];
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
        document.getElementById("seleccionados").children[1].insertRow(-1).innerHTML = '<tr><td><input type="text" name="nombres[]" readonly class="form-control-plaintext nombre" value="' + contenido[k][0] + '"></td><td>' + contenido[k][1] + '</td><td><input class="btn btn-danger btn-quitar" type="button" value="Quitar" onclick = \'quitar("' + contenido[k][0] + '", "' + contenido[k][1] + '")\'></td></tr>';
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
        document.getElementById('fila_cero').style.display = 'block';

        // Ocultamos el botón de generar horario
        document.getElementById('enviar').style.display = 'none';

        // Ocultamos el pie de la tabla
        document.getElementById('pie').style.display = 'none';
    }

}

$(document).ready(function() {
    $('#cursos').DataTable({
        language: idioma_espanol,
        responsive: true,
        // dom: "frtpi"
    });
});

var idioma_espanol = {
    "sProcessing": "Procesando...",
    "sLengthMenu": "Mostrar _MENU_ registros",
    "sZeroRecords": "No se encontraron resultados",
    "sEmptyTable": "Ningún dato disponible en esta tabla",
    "sInfo": "Registros del _START_ al _END_ de _TOTAL_",
    "sInfoEmpty": "Registros del 0 al 0 de 0",
    "sInfoFiltered": "(total global: _MAX_)",
    "sInfoPostFix": "",
    "sSearch": "Buscar curso:",
    "sUrl": "",
    "sInfoThousands": ",",
    "sLoadingRecords": "Cargando...",
    "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Anterior"
    },
    "oAria": {
        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
    },
    "buttons": {
        "copy": "Copiar",
        "colvis": "Visibilidad"
    }
}


// Función del buscador
// $(document).ready(function() {
//     $("#buscador").keyup(function() {
//         _this = this;
//         // Mostrar solamente los TR correctos y ocultar el resto
//         $.each($("#cursos tbody tr"), function() {
//             if ($(this).text().toLowerCase().indexOf($(_this).val().toLowerCase()) === -1)
//                 $(this).hide();
//             else
//                 $(this).show();
//         });
//     });
// });