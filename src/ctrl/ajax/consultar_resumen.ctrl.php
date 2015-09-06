<?php

require_once '../../../config.php';

$respuesta = array();

//$hash = filter_input(INPUT_POST, "hash");
//$asistencias = Reunion::consultar_asistencias($hash);
//
//if (!is_null($reunion)) {
//    $respuesta["exito"] = "ok";
//    $datos = array();
//    $json_asistencias = array();
//    foreach ($asistencias as $asistencia) {
//        
//    }
//    $respuesta["datos"] = array(
//        "asistencias" => $asisntecias
//    );
//    
//} else {
//    $respuesta["exito"] = "error";
//    $respuesta["mensaje"] = "No exite tal reunión";
//}
//echo json_encode($respuesta);

$hash = "66844feb2b9f092c35fdad87a527ae14";
$resumen = Reunion::consultar_resumen($hash);
echo "<pre>";
print_r($resumen);
echo "</pre>";
