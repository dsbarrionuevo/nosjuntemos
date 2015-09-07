<?php

if (basename($_SERVER['PHP_SELF']) == 'config.php') {
    die('Acceso incorrecto a la aplicación.');
}

$RUTA_SERVIDOR = dirname(__FILE__) . '/';
$RUTA_WEB = 'http://' . $_SERVER['HTTP_HOST'] . '/nosjuntemos';

function __autoload($nombre_clase) {
    global $RUTA_SERVIDOR;
    $resultado = '';
    $ruta_clases = $RUTA_SERVIDOR . "/src/clases/";
    $caracteres = str_split($nombre_clase);
    for ($i = 0; $i < count($caracteres); $i++) {
        if (ctype_upper($caracteres[$i]) && $i !== 0) {
            $resultado .= '_';
        }
        $resultado .= strtolower($caracteres[$i]);
    }
    require_once $RUTA_SERVIDOR . '/src/clases/' . $resultado . '.class.php';
}

Conexion::set_default_conexion("nosjuntemos", Conexion::init("localhost", "root", "", "nosjuntemos"));

session_start();
