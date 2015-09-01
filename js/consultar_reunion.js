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
        }
    });

    function obtenerHora(cadena) {
        var partes = cadena.split(":");
        return partes[0];
    }

});