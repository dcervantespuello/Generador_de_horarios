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
    }

}

// function removeItemFromArr(arr, item) {
//     var i = arr.indexOf(item);

//     if (i !== -1) {
//         arr.splice(i, 1);
//     }
// }

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