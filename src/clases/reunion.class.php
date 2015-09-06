<?php

class Reunion {

    private $_id;
    private $_hash;
    private $_nombre;
    private $_lugar;
    private $_hora_inicio;
    private $_hora_fin;
    private $_dias;
    private $_fecha_reunion;
    private $_fecha_creacion;

    function __construct() {
        $this->_id = null;
        $this->_hash = null;
        $this->_nombre = null;
        $this->_lugar = null;
        $this->_hora_inicio = null;
        $this->_hora_fin = null;
        $this->_dias = array();
        $this->_fecha_reunion = null;
        $this->_fecha_creacion = null;
    }

    function get_id() {
        return $this->_id;
    }

    function get_hash() {
        return $this->_hash;
    }

    function get_nombre() {
        return $this->_nombre;
    }

    function get_lugar() {
        return $this->_lugar;
    }

    function get_hora_inicio() {
        return $this->_hora_inicio;
    }

    function get_hora_fin() {
        return $this->_hora_fin;
    }

    function get_dias() {
        return $this->_dias;
    }

    function get_fecha_reunion() {
        return $this->_fecha_reunion;
    }

    function get_fecha_creacion() {
        return $this->_fecha_creacion;
    }

    function set_id($_id) {
        $this->_id = $_id;
    }

    function set_hash($_hash) {
        $this->_hash = $_hash;
    }

    function set_nombre($_nombre) {
        $this->_nombre = $_nombre;
    }

    function set_lugar($_lugar) {
        $this->_lugar = $_lugar;
    }

    function set_hora_inicio($_hora_inicio) {
        $this->_hora_inicio = $_hora_inicio;
    }

    function set_hora_fin($_hora_fin) {
        $this->_hora_fin = $_hora_fin;
    }

    function set_dias($_dias) {
        $this->_dias = $_dias;
    }

    function set_fecha_reunion($_fecha_reunion) {
        $this->_fecha_reunion = $_fecha_reunion;
    }

    function set_fecha_creacion($_fecha_creacion) {
        $this->_fecha_creacion = $_fecha_creacion;
    }

    public static function insertar($nombre, $lugar, $fecha, $hora_inicio, $hora_fin, $dias) {
        $hash_generado = md5(time());
        $conexion = Conexion::get_instancia();
        $conexion->transaccion_comenzar();
        $transaccion_exitosa = true;
        $datos = array(
            "nombre" => $nombre,
            "lugar" => $lugar,
            "hash" => $hash_generado,
            "fecha_reunion" => Util::date_to_big_endian($fecha),
            "hora_inicio" => $hora_inicio . ":00:00",
            "hora_fin" => $hora_fin . ":00:00"
        );
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
        if(!$transaccion_exitosa){
            $hash_generado = null;
        }
        return $hash_generado;
    }

    public static function consultar($hash) {
        $conexion = Conexion::get_instancia();
        $consulta = "SELECT r.id, r.hash, r.nombre, r.lugar, r.hora_inicio, r.hora_fin, r.fecha_reunion, r.fecha_creacion "
                . "FROM reuniones AS r "
                . "WHERE r.hash = '{$hash}'";
        $resultados = $conexion->consultar_simple($consulta);
        $reunion = null;
        if (!empty($resultados)) {
            $resultado = $resultados[0];
            $reunion = new Reunion();
            $reunion->set_id($resultado["id"]);
            $reunion->set_hash($resultado["hash"]);
            $reunion->set_nombre($resultado["nombre"]);
            $reunion->set_lugar($resultado["lugar"]);
            $reunion->set_hora_inicio($resultado["hora_inicio"]);
            $reunion->set_hora_fin($resultado["hora_fin"]);
            $reunion->set_fecha_reunion(($resultado["fecha_reunion"]));
            $reunion->set_fecha_creacion(($resultado["fecha_creacion"]));
            $consulta = "SELECT d.nombre "
                    . "FROM reuniones AS r "
                    . "INNER JOIN dias_x_reunion AS dr ON dr.id_reunion = r.id "
                    . "INNER JOIN dias AS d ON d.id = dr.id_dia "
                    . "WHERE r.hash = '{$hash}'";
            $resultados = $conexion->consultar_simple($consulta);
            $dias = array();
            foreach ($resultados as $resultado) {
                $dias[] = $resultado["nombre"];
            }
            $reunion->set_dias($dias);
        }
        return $reunion;
    }

    public static function registrar_asistencia($id_reunion, $nombre, $horarios) {
        $conexion = Conexion::get_instancia();
        $conexion->transaccion_comenzar();
        $transaccion_exitosa = true;
        $datos = array(
            "id_reunion" => $id_reunion,
            "nombre" => $nombre
        );
        if ($conexion->insertar("asistencias", $datos)) {
            $id_asistencia = $conexion->get_id_insercion();
            $dias = array();
            $consulta = "SELECT id, nombre FROM dias";
            $resultados = $conexion->consultar_simple($consulta);
            foreach ($resultados as $resultado) {
                $dias[$resultado["nombre"]] = $resultado["id"];
            }
            foreach ($horarios as $dia) {
                if (isset($dia["intervals"])) {
                    $intervalos = $dia["intervals"];
                    foreach ($intervalos as $intervalo) {
                        $datos = array(
                            "id_asistencia" => $id_asistencia,
                            "id_dia" => $dias[$dia["name"]],
                            "hora_inicio" => $intervalo["initHour"] . ":00:00",
                            "hora_fin" => $intervalo["endHour"] . ":00:00"
                        );
                        if (!$conexion->insertar("horas_x_asistencia", $datos)) {
                            $transaccion_exitosa = false;
                            break;
                        }
                    }
                }
            }
        } else {
            $transaccion_exitosa = false;
        }
        $conexion->transaccion_terminar($transaccion_exitosa);
        return $transaccion_exitosa;
    }

    public static function consultar_asistencias($hash) {
        $asistencias = array();
        $conexion = Conexion::get_instancia();
        $consulta = "SELECT a.id, a.nombre, r.hora_inicio, r.hora_fin "
                . "FROM asistencias AS a "
                . "INNER JOIN reuniones AS r ON a.id_reunion = r.id "
                . "WHERE r.hash = '{$hash}'";
        $resultados = $conexion->consultar_simple($consulta);
        foreach ($resultados as $resultado) {
            $id_asistencia = $resultado["id"];
            $asistencia = Asistencia::consultar($id_asistencia);
            $asistencias[] = $asistencia;
        }
        return $asistencias;
    }

    public static function consultar_resumen($hash) {
        $asistencias = Reunion::consultar_asistencias($hash);
        $reunion = Reunion::consultar($hash);
        $hora_inicio = Reunion::convertir_hora($reunion->get_hora_inicio());
        $hora_fin = Reunion::convertir_hora($reunion->get_hora_fin());
        $resumen = array();
        $conexion = Conexion::get_instancia();
        $consulta = "SELECT id, nombre FROM dias";
        $resultados = $conexion->consultar_simple($consulta);
        foreach ($resultados as $resultado) {
            $dias[$resultado["nombre"]] = $resultado["id"];
            $resumen[$resultado["nombre"]] = array(); //horarios
            for ($i = $hora_inicio; $i < $hora_fin; $i++) {
                $resumen[$resultado["nombre"]][$i] = 0;
            }
        }
        foreach ($asistencias as $asistencia) {
            $dias = $asistencia->get_dias();
            foreach ($dias as $dia) {
                for ($i = $hora_inicio; $i < $hora_fin; $i++) {
                    if ($i >= Reunion::convertir_hora($dia["hora_inicio"]) && $i < Reunion::convertir_hora($dia["hora_fin"])) {
                        $resumen[$dia["nombre"]][$i] = $resumen[$dia["nombre"]][$i] + 1;
                    }
                }
            }
        }
        return $resumen;
    }

    private static function convertir_hora($hora) {
        if (!strpos($hora, ":")) {
            $partes = explode(":", $hora);
            return (int) $partes[0];
        }
        return (int) $hora;
    }

}
