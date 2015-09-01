<?php

class Reunion {

    public static function insertar($nombre, $lugar, $fecha, $hora_inicio, $hora_fin, $dias) {
        $conexion = Conexion::get_instancia();
        $conexion->transaccion_comenzar();
        $transaccion_exitosa = true;
        $datos = array(
            "nombre" => $nombre,
            "lugar" => $lugar,
            "fecha_reunion" => Util::date_to_big_endian($fecha),
            "hora_inicio" => $hora_inicio,
            "hora_fin" => $hora_fin
        );
        echo Util::date_to_big_endian($fecha);
        if ($conexion->insertar("reuniones", $datos)) {
            $id_reunion = $conexion->get_id_insercion();
            for ($i = 0; $i < count($dias); $i++) {
                $datos = array(
                    "id_reunion" => $id_reunion,
                    "id_dia" => $dias[$i]
                );
                if (!$conexion->insertar("dias_x_reunion", $datos)) {
                    $transaccion_exitosa = false;
                    break;
                }
            }
        } else {
            $transaccion_exitosa = false;
        }
        $conexion->transaccion_terminar($transaccion_exitosa);
        return $transaccion_exitosa;
    }

}
