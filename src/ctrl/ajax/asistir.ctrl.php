<?php

require_once '../../../config.php';

$respuesta = array();

$datos = $_POST["datos"];
$hash_reunion = $datos["id"];
$reunion = Reunion::consultar($hash_reunion);
$id_reunion = $reunion->get_id();
$nombre = $datos["nombre"];
$horarios = $datos["horarios"];
if (Reunion::registrar_asistencia($id_reunion, $nombre, $horarios)) {
    $respuesta["exito"] = "ok";
} else {
    $respuesta["exito"] = "error";
    $respuesta["mensaje"] = "Error al tratar de registrar asistencia";
}

//print_r($horarios);
echo json_encode($respuesta);