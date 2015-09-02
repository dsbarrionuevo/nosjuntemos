$(document).ready(function () {

    $("#hash").hide();
    $.ajax({
        url: "ajax/consultar_reunion.ctrl.php",
        type: "post",
        data: {
            hash: $("#hash").html().trim()
        }
    }).done(function (respuesta) {
        var respuesta = JSON.parse(respuesta);
        if (respuesta.exito === "ok") {
            var reunion = respuesta.datos;
            Scheduler.create({
                table: $("#tabla_horario"),
                days: reunion.dias,
                initTime: obtenerHora(reunion.hora_inicio),
                finishTime: obtenerHora(reunion.hora_fin)
            });
            $("#nombre").html(reunion.nombre);
            $("#lugar").html(reunion.lugar);
            $("#fecha").html(reunion.fecha_reunion);
        }
    });

    $("#btnAsistir").click(function () {
        console.log(Scheduler.getMarks());
        $.ajax({
            url: "ajax/asistir.ctrl.php",
            type: "post",
            data: {
                datos: {
                    nombre: $("#txtNombre").val().trim(),
                    hash: $("#hash").html().trim(),
                    horarios: Scheduler.getMarks()
                }
            }
        }).done(function (respuesta) {
            console.log(respuesta);
//            var respuesta = JSON.parse(respuesta);
//            if (respuesta.exito === "ok") {
//            }
        });
    });

    function obtenerHora(cadena) {
        var partes = cadena.split(":");
        return partes[0];
    }

});