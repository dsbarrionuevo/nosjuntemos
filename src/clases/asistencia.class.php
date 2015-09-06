<?php

class Asistencia {

    private $_id;
    private $_nombre;
    private $_fecha;
    private $_dias;

    function __construct() {
        $this->_id = null;
        $this->_nombre = null;
        $this->_fecha = null;
        $this->_dias = array();
    }

    function get_id() {
        return $this->_id;
    }

    function get_nombre() {
        return $this->_nombre;
    }

    function get_fecha() {
        return $this->_fecha;
    }

    function get_dias() {
        return $this->_dias;
    }

    function set_id($_id) {
        $this->_id = $_id;
    }

    function set_nombre($_nombre) {
        $this->_nombre = $_nombre;
    }

    function set_fecha($_fecha) {
        $this->_fecha = $_fecha;
    }

    function set_dias($_dias) {
        $this->_dias = $_dias;
    }

    public static function consultar($id) {
        $conexion = Conexion::get_instancia();
        $consulta = "SELECT id, nombre, fecha "
                . "FROM asistencias "
                . "WHERE id = {$id}";
        $resultados = $conexion->consultar_simple($consulta);
        $asistencia = null;
        if (!empty($resultados)) {
            $asistencia = new Asistencia();
            $asistencia->set_id($resultados[0]["id"]);
            $asistencia->set_nombre($resultados[0]["nombre"]);
            $asistencia->set_fecha($resultados[0]["fecha"]);
            $consulta = "SELECT ha.id_dia, d.nombre, ha.hora_inicio, ha.hora_fin "
                    . "FROM horas_x_asistencia AS ha "
                    . "INNER JOIN dias AS d ON ha.id_dia = d.id "
                    . "WHERE id_asistencia = {$id} "
                    . "ORDER BY id_dia";
            $resultados = $conexion->consultar_simple($consulta);
            $dias = array();
            foreach ($resultados as $resultado) {
                $dia = array(
                    "id_dia" => $resultado["id_dia"],
                    "nombre" => $resultado["nombre"],
                    "hora_inicio" => $resultado["hora_inicio"],
                    "hora_fin" => $resultado["hora_fin"]
                );
                $dias[] = $dia;
            }
            $asistencia->set_dias($dias);
        }
        return $asistencia;
    }

}
