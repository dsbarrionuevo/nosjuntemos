$(document).ready(function () {
//    var tabla = $("#tabla_horario");
//    for (var i = 1; i <= 24; i++) {
//        var horaInicio = i + ":00";
//        var filaHtml;
//        if (i < 24) {
//            filaHtml = "<tr><td><label>" + horaInicio + "</label></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
//        } else {
//            filaHtml = "<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
//        }
//        tabla.find("tbody").append(filaHtml);
//    }
    Scheduler.create({
        table: $("#tabla_horario"),
        days: ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"],
        initTime: 12,
        finishTime: 18
    });
});