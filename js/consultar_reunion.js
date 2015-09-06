$(document).ready(function () {

    var colores = ["#E3F2FD", "#BBDEFB", "#90CAF9", "#64B5F6", "#42A5F5", "#2196F3", "#1E88E5", "#1976D2", "#1565C0", "#0D47A"];

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

            //ahora cargo las asistencias de esta reunion
            $.ajax({
                url: "ajax/consultar_resumen.ctrl.php",
                type: "post",
                data: {
                    hash: $("#hash").html().trim()
                }
            }).done(function (respuesta) {
                var respuesta = JSON.parse(respuesta);
                if (respuesta.exito === "ok") {
                    var resumen = respuesta.datos;
                    var total = 0;
                    for (var i = 0; i < resumen.length; i++) {
                        var intervalos = resumen[i].intervalos;
                        for (var j in intervalos) {
                            total += intervalos[j];
                        }
                    }
                    for (var i = 0; i < resumen.length; i++) {
                        var nombreDia = resumen[i].nombre;
                        var intervalos = resumen[i].intervalos;
                        for (var horaInicio in intervalos) {
                            var cantidad = intervalos[horaInicio];
                            if (cantidad > 0) {
                                var color = calcularColor(cantidad, total);
                                //Scheduler.getTimecell(nombreDia, horaInicio).css("border-bottom", "4px solid " + color);
                                Scheduler.getTimecell(nombreDia, horaInicio).css("background-color", color);
                            }
                        }
                    }
                }
            });
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

    function calcularColor(cantidad, total) {
        var valor = parseInt(((cantidad * 100) / total) / 10);
        return colores[valor];
    }

});