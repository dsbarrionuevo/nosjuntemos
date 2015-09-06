<?php

require_once '../../../config.php';

$respuesta = array();

$hash = filter_input(INPUT_POST, "id");
//$hash = $_REQUEST["id"];
$resumen = Reunion::consultar_resumen($hash);

if (!empty($resumen)) {
    $respuesta["exito"] = "ok";
    $resumen_json = array();
    foreach ($resumen as $dia => $intervalos) {
        $intervalos_json = array();
        foreach ($intervalos as $hora_inicio => $cantidad) {
            $intervalos_json[$hora_inicio] = $cantidad;
        }
        $dia_json = array(
            "nombre" => $dia,
            "intervalos" => $intervalos_json
        );
        $resumen_json[] = $dia_json;
    }
    $respuesta["datos"] = $resumen_json;
} else {
    $respuesta["exito"] = "error";
    $respuesta["mensaje"] = "Error al tratar de ver el resumen de reunión";
}

echo json_encode($respuesta);

//$hash = "66844feb2b9f092c35fdad87a527ae14";
//$resumen = Reunion::consultar_resumen($hash);
//echo "<pre>";
//print_r($resumen);
//echo "</pre>";
