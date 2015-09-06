$(document).ready(function () {

    var colores = ["#E3F2FD", "#BBDEFB", "#90CAF9", "#64B5F6", "#42A5F5", "#2196F3", "#1E88E5", "#1976D2", "#1565C0", "#0D47A"];
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
                    id: idReunion
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
                                var celda = Scheduler.getTimecell(nombreDia, horaInicio);
                                var color = calcularColor(cantidad, total);
                                //celda.css("border-bottom", "4px solid " + color);
                                celda.css("background-color", color);
                                /*
                                 var colorAnterior = celda.css("background-color");
                                 if (compararColores(color, colorAnterior) > 0) {
                                 celda.css("background-color", color);
                                 } else {
                                 celda.css("background-color", colorAnterior);
                                 }*/
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
                    id: idReunion,
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
        console.log(cantidad, total);
        var valor = parseInt(((cantidad * 100) / total) / 10);
        return colores[valor];
    }

    //http://stackoverflow.com/questions/12043187/how-to-check-if-hex-color-is-too-black
    function getDarkness(color) {
        var c = color.substring(1);      // strip #
        var rgb = parseInt(c, 16);   // convert rrggbb to decimal
        var r = (rgb >> 16) & 0xff;  // extract red
        var g = (rgb >> 8) & 0xff;  // extract green
        var b = (rgb >> 0) & 0xff;  // extract blue
        var luma = 0.2126 * r + 0.7152 * g + 0.0722 * b; // per ITU-R BT.709
        return luma;
    }

    function compararColores(color1, color2) {
        color1 = getDarkness(color1);
        color2 = getDarkness(color2);
        if (color1 == color2) {
            return 0;
        } else if (color1 > color2) {
            return 1;
        }
        return -1;
    }

});