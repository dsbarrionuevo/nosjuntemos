<?php

require_once '../../config.php';

$tmpl_dias = Dia::consultar_dias();

$btn_crear = filter_input(INPUT_POST, "btn_crear");
if (isset($btn_crear)) {
    $nombre = filter_input(INPUT_POST, "txt_nombre");
    $lugar = filter_input(INPUT_POST, "txt_lugar");
    $hora_inicio = filter_input(INPUT_POST, "txt_hora_inicio");
    $hora_fin = filter_input(INPUT_POST, "txt_hora_fin");
    $fecha = filter_input(INPUT_POST, "txt_fecha");
    $dias = array();
    for ($i = 0; $i < count($tmpl_dias); $i++) {
        $dia = filter_input(INPUT_POST, "chk_dia_" . $tmpl_dias[$i]["id"]);
        if (isset($dia)) {
            $dias[] = $tmpl_dias[$i]["id"];
        }
    }
    $hash = Reunion::insertar($nombre, $lugar, $fecha, $hora_inicio, $hora_fin, $dias);
    if (!is_null($hash)) {
        //exito
        header("Location: " . $RUTA_WEB . "/src/ctrl/consultar_reunion.ctrl.php?id=" . $hash);
        exit();
    } else {
        $tmpl_mensaje = "Problemas al tratar de crear la reunión";
    }
}

require_once $RUTA_SERVIDOR . '/tmpl/crear_reunion.tmpl.php';
