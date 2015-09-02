<?php

require_once '../../../config.php';

$respuesta = array();

$hash = filter_input(INPUT_POST, "hash");
$reunion = Reunion::consultar($hash);

if (!is_null($reunion)) {
    $respuesta["exito"] = "ok";
    $datos = array();
    $respuesta["datos"] = array(
        "nombre" => $reunion->get_nombre(),
        "lugar" => $reunion->get_lugar(),
        "hora_inicio" => $reunion->get_hora_inicio(),
        "hora_fin" => $reunion->get_hora_fin(),
        "fecha_reunion" => $reunion->get_fecha_reunion(),
        "dias" => $reunion->get_dias()
    );
} else {
    $respuesta["exito"] = "error";
    $respuesta["mensaje"] = "No exite tal reunión";
}

echo json_encode($respuesta);