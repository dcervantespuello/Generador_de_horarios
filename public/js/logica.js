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

        // Quita el parrafo si el número de filas en la tabla de seleccionados era cero
        if (cantidad == 0) {
            fila_cero.innerHTML = "";
            document.getElementById('enviar').style.display = 'block';
        }

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

    // Cantidad de filas actual en la tabla de seleccionados
    var cantidad = document.getElementById('seleccionados').children[1].children.length;

    // Quita el parrafo si el número de filas en la tabla de seleccionados era cero
    if (cantidad == 0) {
        document.getElementById('fila_cero').innerHTML = "¡No hay cursos seleccionados!";
        document.getElementById('enviar').style.display = 'none';
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

// function agregar(nombre, creditos) {

//     var cuerpo = document.getElementById('seleccionados').children[1];
//     var cantidad = cuerpo.children.length;

//     // Tomando todo el contenido de la tabla de la lista de cursos seleccionados
//     var contenido = [];

//     var filas = document.getElementById('seleccionados').children[1].children;

//     for (var i = 0; i < filas.length; i++) {
//         contenido[i] = [filas[i].children[0].textContent.trim(), filas[i].children[1].textContent.trim()];
//     }


//     $.ajax({
//         url: "{{ route('agregar') }}",
//         dataType: 'json',
//         data: {
//             'nombre': nombre,
//             'creditos': creditos,
//             'cantidad': cantidad,
//             'contenido': JSON.stringify(contenido)
//         },
//         type: 'post',
//         success: function(response) {
//             console.log(response);
//             var cantidad = response.cantidad;

//             if (cantidad == 0) {
//                 document.getElementById('fila_cero').innerHTML = "";
//             }

//             document.getElementById("seleccionados").children[1].insertRow(-1).innerHTML =
//                 '<tr><td>' + response.nombre + '</td><td>' + response.creditos +
//                 '</td><td><input class="btn btn-danger btn-quitar" type="button" value="Quitar" onclick = \'quitar("' +
//                 response.nombre + '", "' + response.creditos + '")\'></td></tr>';
//         },
//         statusCode: {
//             404: function() {
//                 alert('web not found');
//             }
//         },
//         error: function(x, xs, xt) {
//             window.open(JSON.stringify(x));
//             //alert('error: ' + JSON.stringify(x) +"\n error string: "+ xs + "\n error throwed: " + xt);
//         }
//     });

// }