$(document).ready(function () {

    var colores = ["#E3F2FD", "#BBDEFB", "#90CAF9", "#64B5F6", "#42A5F5", "#2196F3", "#1E88E5", "#1976D2", "#1565C0", "#0D47A1"];
    var idReunion = $("#idReunion").html().trim();

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
                addClickEvent: false
            });
            $("#nombre").html(reunion.nombre);
            $("#lugar").html(reunion.lugar);
            $("#fecha").html(reunion.fecha_reunion);

            //ahora cargo las asistencias de esta reunion
            $.ajax({
                url: "ajax/consultar_resumen.ctrl.php",
                type: "post",
                data: {
                    id: idReunion
                }
            }).done(function (respuesta) {
                var respuesta = JSON.parse(respuesta);
                if (respuesta.exito === "ok") {
                    var resumen = respuesta.datos;
                    var maximo = 0;
                    for (var i = 0; i < resumen.length; i++) {
                        var intervalos = resumen[i].intervalos;
                        for (var j in intervalos) {
                            if (intervalos[j] > maximo)
                                maximo = intervalos[j];
                        }
                    }
                    for (var i = 0; i < resumen.length; i++) {
                        var nombreDia = resumen[i].nombre;
                        var intervalos = resumen[i].intervalos;
                        for (var horaInicio in intervalos) {
                            var cantidad = intervalos[horaInicio];
                            if (cantidad > 0) {
                                var celda = Scheduler.getTimecell(nombreDia, horaInicio);
                                var color = calcularColor(cantidad, maximo);
                                //celda.css("border-bottom", "4px solid " + color);
                                celda.css("background", color);
                                celda.children("span").html(cantidad);
                                celda.children("span").css("display", "inline");
                            }
                        }
                    }
                }
            });
        }
    });

    function obtenerHora(cadena) {
        var partes = cadena.split(":");
        return partes[0];
    }

    function calcularColor(cantidad, total) {
        //console.log(cantidad, total);
        var valor = parseInt((cantidad * 10) / total) - 1;
        return colores[valor];
    }

});