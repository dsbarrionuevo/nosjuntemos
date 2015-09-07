$(document).ready(function () {

    var idReunion = $("#idReunion").html().trim();

    $("#mensaje").hide();
    $("#idReunion").hide();
    $.ajax({
        url: "ajax/consultar_reunion.ctrl.php",
        type: "post",
        data: {
            id: idReunion
        }
    }).done(function (respuesta) {
        var respuesta = JSON.parse(respuesta);
        if (respuesta.exito === "ok") {
            var reunion = respuesta.datos;
            Scheduler.create({
                table: $("#tabla_horario"),
                days: reunion.dias,
                initTime: obtenerHora(reunion.hora_inicio),
                finishTime: obtenerHora(reunion.hora_fin),
                addClickEvent: true
            });
            $("#nombre").html(reunion.nombre);
            $("#lugar").html(reunion.lugar);
            $("#fecha").html(reunion.fecha_reunion);
        }
    });

    $("#btnAsistir").click(function () {
        //valido
        if (Scheduler.getMarksCount() > 0) {

            $("#txtNombre").prop("disabled", true);
            $.ajax({
                url: "ajax/asistir.ctrl.php",
                type: "post",
                data: {
                    datos: {
                        nombre: $("#txtNombre").val().trim(),
                        id: idReunion,
                        horarios: Scheduler.getMarks()
                    }
                }
            }).done(function (respuesta) {
                var respuesta = JSON.parse(respuesta);
                if (respuesta.exito === "ok") {
                    location.href = "consultar_reunion.ctrl.php?id=" + idReunion;
                } else {
                    $("#mensaje").html(respuesta.mensaje);
                    $("#mensaje").show();
                }
            });
        }
    });

    function obtenerHora(cadena) {
        var partes = cadena.split(":");
        return partes[0];
    }

});