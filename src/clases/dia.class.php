<?php

class Dia {

    public static function consultar_dias() {
        $conexion = Conexion::get_instancia();
        $consulta = "SELECT id, nombre FROM diaS";
        $resultados = $conexion->consultar_simple($consulta);
        $dias = [];
        foreach ($resultados as $resultado) {
            $dias[] = array("id" => $resultado["id"], "nombre" => $resultado["nombre"]);
        }
        return $dias;
    }

}
