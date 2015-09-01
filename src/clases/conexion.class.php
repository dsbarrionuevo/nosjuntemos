<?php

/**
 * Se encarga de todos los accesos a la base de datos. Puede consultar, 
 * insertar y actualizar. También permite el uso de transacciones sólo si la 
 * versión de php instalada en el servidor es como mínimo 5.5.0
 * 
 * #¿Cómo conectarme con una base de datos?
 *      Conexion::set_default_conexion("Conexion")
 *      Conexion::init('host', 'nombre_usuario', 'clave_usuario', 'base_datos');
 *      $conexion = Conexion::get_instacia();
 *      $conexion->consultar_simple("SELECT * FROM mi_tabla");
 * 
 * #¿Cómo hacer uso de transacciones? (Sólo php versión 5.5.0)
 *      $conexion = Conexion::get_instacia();
 *      $conexion->transaccion_comenzar();
 *      //realizar las inserciones, actualizaciones, etc.
 *      if($conexion->ok()){
 *          $conexion->transaccion_confirmar();
 *      }else{
 *          $conexion->transaccion_revertir();      
 *      }
 *
 * @author Diego Barrionuevo y Parisi Germán
 * @version 1.2
 */
class Conexion {

    const FILTRO_BUSQUEDA_COINCIDIR_STRING_COMPLETO = 1;
    const FILTRO_BUSQUEDA_COINCIDIR_STRING_IZQUIERDO = 2;
    const FILTRO_BUSQUEDA_COINCIDIR_STRING_DERECHO = 3;
    const FILTRO_BUSQUEDA_IGUALDAD = 4;
    const FILTRO_BUSQUEDA_MAYOR_QUE = 5;
    const FILTRO_BUSQUEDA_MENOR_QUE = 6;
    //valores por defecto
    const VALOR_INVALIDO_ID_INSERCION = -1;
    const VALOR_POR_DEFECTO_MODO_AUTOCONEXCION = true;
    const VALOR_INVALIDO_CANTIDAD_FILAS_AFECTADAS = -1;
    const VALOR_INVALIDO_CANTIDAD_FILAS_OBTENIDAS = -1;

    private static $_instancias = array();
    private static $_conexion_default = '';
    private $_host = 'localhost';
    private $_nombre_usuario = 'root';
    private $_clave_usuario = '';
    private $_base_datos = '';
    private $_modo_desarrollo = false;
    private $_conjunto_caracteres = 'utf8';
    private $_conexion;
    private $_modo_autoconexion;
    private $_id_insercion;
    private $_cantidad_filas_afectadas;
    private $_cantidad_filas_obtenidas;
    private $_error;

    /**
     * Devuele la única instancia actual de la clase Conexion. Para que 
     * funciona correctamente es necesario que estén setados previamente los 
     * parmámetros de conexión, estos son: host, nombre de usuario, clave de 
     * usuario, y base de datos. Los mismos son seteados con el método estático 
     * Conexion::init(host,nombre_usuario,clave_usuario,base_datos).
     * 
     * #NOTA: Previamente se debe llamar al método estático init() para setear 
     * los parámetros de la conexión.
     * 
     * @return Conexion Instancia única de Conexion.
     */
    public static function get_instancia($nombre_conexion = '') {
        if ($nombre_conexion == '') {
            $nombre_conexion = Conexion::$_conexion_default;
        }
        if (isset(self::$_instancias[$nombre_conexion])) {
            $instancia_actual = self::$_instancias[$nombre_conexion];
            if (is_null($instancia_actual->_host) || is_null($instancia_actual->_nombre_usuario) || is_null($instancia_actual->_clave_usuario) || is_null($instancia_actual->_base_datos)) {
                die("Error Fatal: Error al tratar de iniciar la conexión con la base de datos");
            }
            return $instancia_actual;
        } else {
            die("Error Fatal: Error al tratar de iniciar la conexión con la base de datos");
        }
    }

    public static function agregar_instancia($nombre_conexion, $conexion) {
        self::$_instancias[$nombre_conexion] = $conexion;
    }

    /**
     * Setea los parámetros de conexion: es muy importante llamar a este método 
     * antes de realiza cualquier conexión o consulta con la base de datos.
     * @param string $_host Nombre del host donde recide la base de datos.
     * @param string $_nombre_usuario Nombre del usuario de la base de datos.
     * @param string $_clave_usuario Clave del usuario.
     * @param string $_base_datos Nombre de la base de datos a conectarme.
     * @param boolean $_modo_desarrollo Modo de desarrollo o producción: True 
     * significa que estoy en modo de desarrollo y por lo tanto todos los 
     * mensajes que alertas que notifique la base de datos serán mostrados en 
     * detalle. False significa que los mensajes serán mostrados de manera 
     * resumida sin dar mucha información al usuario final (es el modo en que 
     * se debería configurar para cuando el sistema esté en producción). Valor 
     * por defecto: false.
     * @param string $conjunto_caracteres Conjunto de caracteres a utilizar en 
     * la conexión con la base de datos. Valor por defecto: 'utf8'.
     */
    public static function init($_host, $_nombre_usuario, $_clave_usuario, $_base_datos, $_modo_desarrollo = false, $conjunto_caracteres = 'utf8') {
        return new Conexion($_host, $_nombre_usuario, $_clave_usuario, $_base_datos, $_modo_desarrollo, $conjunto_caracteres);
    }

    public static function set_default_conexion($nombre_conexion, $conexion) {
        Conexion::$_conexion_default = $nombre_conexion;
        Conexion::agregar_instancia(Conexion::$_conexion_default, $conexion);
    }

    /**
     * 
     * @return \Conexion
     */
    public static function get_default_conexion() {
        return Conexion::$_conexion_default;
    }

    private function __construct($_host, $_nombre_usuario, $_clave_usuario, $_base_datos, $_modo_desarrollo = false, $conjunto_caracteres = 'utf8') {
        $this->_host = $_host;
        $this->_nombre_usuario = $_nombre_usuario;
        $this->_clave_usuario = $_clave_usuario;
        $this->_base_datos = $_base_datos;
        $this->_modo_desarrollo = $_modo_desarrollo;
        $this->_conjunto_caracteres = $conjunto_caracteres;
        //Todos estos son valores de instancia por defecto.
        $this->_conexion = null;
        $this->_id_insercion = Conexion::VALOR_INVALIDO_ID_INSERCION;
        $this->_modo_autoconexion = Conexion::VALOR_POR_DEFECTO_MODO_AUTOCONEXCION;
        $this->_cantidad_filas_afectadas = Conexion::VALOR_INVALIDO_CANTIDAD_FILAS_AFECTADAS;
        $this->_cantidad_filas_obtenidas = Conexion::VALOR_INVALIDO_CANTIDAD_FILAS_OBTENIDAS;
        $this->_error = array();
    }

    public function conectar() {
        $this->_conexion = new mysqli($this->_host, $this->_nombre_usuario, $this->_clave_usuario, $this->_base_datos);
        if (!is_null($this->_conexion->connect_error)) {
            $this->error("Error al tratar de conectar con la base de datos, con "
                    . "los siguientes parámetros de conexión: "
                    . "-host:" . $this->_host . ", "
                    . "-usuario:" . $this->_nombre_usuario . ", "
                    . "-base_datos:" . $this->_base_datos . ". "
                    , $this->_conexion->connect_errno, $this->_conexion->connect_error);
            return false;
        }
        $this->_conexion->set_charset($this->_conjunto_caracteres);
        return true;
    }

    public function cerrar() {
        if (!is_null($this->_conexion) && !$this->_conexion->close()) {
            $this->error("Error al tratar de cerrar la conexión");
            return false;
        }
        $this->_conexion = null;
        return true;
    }

    /**
     * Realiza una consulta SELECT a la base de datos.
     * @param string $tabla Nombre de la tabla de la cual se hará la consulta.
     * @param mixed $campos Puede ser un string con los nombres de los campos 
     * separados por coma, o también puede ser un array donde cada elemento es
     * un nombre de campo. En ambos casos es posible usar alias a los nombres de
     * campo.
     * @param mixed $filtros Puede ser un string representando la condición tal 
     * cual se ejecutará en la base de datos, o puede ser un array de filtros 
     * de búsqueda en el que cada filtro es un array que puede ser tanto 
     * asociativo (con los índices: 'campo' que representa el nombre del campo, 
     * 'filtro' que representa el tipo de filtro espicificado como constante de 
     * esta clase, 'valor' que representa el valor de comparación de la 
     * búsqueda) o indexado con el orden (campor, filtro, valor). Ejemplo de su 
     * uso:
     * #Ejemplo filtro por apellido terminado en 'ez' y a la vez que tenga entre
     * 21 y 35 años:
     *  array(
     *      array(
     *          'campo' => 'apellido',
     *          'filtro' => Conexion::FILTRO_BUSQUEDA_COINCIDIR_STRING_DERECHO,
     *          'valor' => 'ez'
     *          ),
     *      array(
     *          'edad',
     *          Conexion::FILTRO_BUSQUEDA_MAYOR_QUE,
     *          20
     *          ),
     *      array(
     *          'edad',
     *          Conexion::FILTRO_BUSQUEDA_MENOR_QUE,
     *          36
     *          )
     *  );
     * @param mixed $orden Puede ser el nombre del campo como string, o un 
     * número entero que representa el campo en la lista de selección.
     * @param int $limite_resultados La cantidad de resultados a mostrar como 
     * límite.
     * @return array Si tuvo éxito, devuelve el resultado de la consulta como 
     * un array indexado de filas, donde cada fila es un array asociativo con 
     * el nombre del campo como clave, y como valor del campo como valor.
     */
    public function consultar($tabla, $campos, $filtros = null, $orden = null, $limite_resultados = null) {
        $this->autoconectar();
        $consulta = "SELECT ";
        $lista_columnas = "";
        if (is_array($campos)) {
            for ($i = 0; $i < count($campos); $i++) {
                $lista_columnas .= "{$campos[$i]}, ";
            }
        } else {
            $lista_columnas = $campos;
        }
        $consulta .= rtrim($lista_columnas, ", ");
        $consulta .= " FROM {$tabla}";
        if (!is_null($filtros)) {
            $consulta .= " WHERE 1=1 ";
            if (is_array($filtros)) {
                $consulta .= $this->filtrar_resultados($filtros);
            } else {
                //si es un string, lo anexo como viene
                $consulta .= " AND {$filtros}";
            }
        }
        if (!is_null($orden)) {
            $consulta .= " ORDER BY {$orden}";
        }
        if (!is_null($limite_resultados)) {
            $consulta .= " LIMIT {$limite_resultados}";
        }
        return $this->consultar_simple($consulta);
    }

    /**
     * Realiza una consulta SELECT a la base de datos para obtener un sólo 
     * registro (por ejemplo si estamos buscando un registro por su clave 
     * primaria).
     * @param string $tabla Nombre de la tabla de la cual se hará la consulta.
     * @param mixed $campos Puede ser un string con los nombres de los campos 
     * separados por coma, o también puede ser un array donde cada elemento es
     * un nombre de campo. En ambos casos es posible usar alias a los nombres de
     * campo.
     * @param string $campo_unico Nombre del campo cuyo valor se supone único.
     * @param mixed $valor_unico Valor único buscado. Puede ser un entero, un 
     * string, etc.
     * @return array Si tuvo éxito, devuelve el resultado de la consulta como 
     * un array indexado de filas, donde cada fila es un array asociativo con 
     * el nombre del campo como clave, y como valor del campo como valor.
     */
    public function consultar_unico($tabla, $campos, $campo_unico, $valor_unico) {
        $this->autoconectar();
        $filtro = array('campo' => $campo_unico, 'filtro' => self::FILTRO_BUSQUEDA_IGUALDAD, 'valor' => $valor_unico);
        return $this->consultar($tabla, $campos, array($filtro));
    }

    /**
     * Realiza una consulta SELECT a la base de datos para obtener un sólo 
     * registro (por ejemplo si estamos buscando un registro por su clave 
     * primaria).
     * @param string $consulta Consulta SELECT a ser ejecutada sobre la base de 
     * datos.
     * @return mixed Si tuvo éxito, devuelve el resultado de la consulta como 
     * un array indexado de filas, donde cada fila es un array asociativo con 
     * el nombre del campo como clave, y como valor del campo como valor, 
     * incluso si tuvo éxito al realizar la consulta pero no existen resultados 
     * devuelve un array vacío. Si no tuvo éxito, por un fallo con la consulta 
     * o lo que fuera, devuelve false. Nota: nunca devuelte true, sólo puede 
     * devolver un array de resultados o false.
     */
    public function consultar_simple($consulta) {
        $this->autoconectar();
        $this->_cantidad_filas_obtenidas = Conexion::VALOR_INVALIDO_CANTIDAD_FILAS_OBTENIDAS;
        $conjunto_resultados = $this->_conexion->query($consulta);
        $resultado = array();
        if ($conjunto_resultados === false) {
            $this->error("Error al ejecutar consulta");
            return false;
        } else {
            $this->_cantidad_filas_obtenidas = $conjunto_resultados->num_rows;
            for ($i = 0; $i < $conjunto_resultados->num_rows; $i++) {
                $resultado[] = $conjunto_resultados->fetch_assoc();
            }
        }
        $conjunto_resultados->close();
        if ($this->_modo_autoconexion) {
            $this->cerrar();
        }
        return $resultado;
    }

    /**
     * Inserta en la tabla dada como parámetro los datos especificados. Es 
     * equivalente a ejecutar la sentecia INSERT en la base de datos. Al hacer 
     * la inserción con éxito, setea los valores id_insercion y cantidad de 
     * filas afectadas.
     * @param string $tabla Nombre de la tabla en la cual se hará la inserción.
     * @param array $datos Array asociativo con el nombre del campo como clave y
     * el valor de campo de inserción como valor.
     * @return boolean Devuelve true si pudo insertar el registro, false en 
     * caso contrario.
     */
    public function insertar($tabla, $datos) {
        $this->autoconectar();
        $insercion = "INSERT INTO {$tabla} ";
        $nombres_campos = "";
        $valores_campos = "";
        foreach ($datos as $nombre_campo => $valor_campo) {
            if (is_null($valor_campo) || (is_string($valor_campo) && $valor_campo === '') || (is_int($valor_campo) && $valor_campo === -1)) {
                //$valores_campos .= " NULL, 2;/
            } else {
                $nombres_campos .= "{$nombre_campo}, ";
                if (is_numeric($valor_campo)) {
                    $valores_campos .= "{$valor_campo}, ";
                } else {
                    if($nombre_campo != "foto"){
                        $valores_campos .= "'{$this->_conexion->real_escape_string($valor_campo)}', ";
                    }else{
                        $valores_campos .= "'{$valor_campo}', ";
                    }
                }
            }
        }
        $insercion .= " (" . rtrim($nombres_campos, ", ") . ") ";
        $insercion .= "VALUES (" . rtrim($valores_campos, ", ") . ")";
        return $this->insertar_simple($insercion);
    }

    /**
     * Ejecuta la sentecia de inserción (tipo INSERT) en la base de datos. Al 
     * hacer la inserción con éxito, setea los valores id_insercion y cantidad 
     * de filas afectadas.
     * @param string $insercion Sentencia de inserción.
     * @return boolean Devuelve true si pudo insertar el registro, false en 
     * caso contrario.
     */
    public function insertar_simple($insercion) {
        $this->autoconectar();
        $this->_id_insercion = Conexion::VALOR_INVALIDO_ID_INSERCION;
        if (!$this->_conexion->query($insercion)) {
            $this->error("Error al ejecutar inserción [{$insercion}]");
            return false;
        }
        $this->_id_insercion = $this->_conexion->insert_id;
        $this->_cantidad_filas_afectadas = $this->_conexion->affected_rows;
        if ($this->_modo_autoconexion) {
            $this->cerrar();
        }
        return true;
    }

    /**
     * Actualiza la tabla pasada como parámetro con los datos indicados. 
     * También permite aplicar filtros de condición para ser más específico al 
     * momento de querer actualizar los registros.  Si tiene éxito, setea la 
     * cantidad de filas afectadas.
     * @param string $tabla Nombre de la tabla cuyos registros se desean 
     * actualizar.
     * @param array $datos Array asociativo donde la clave es el nombre del 
     * campo y el valor es el nuevo valor a obtener en dicho campo.
     * @param mixed $filtros Puede ser un string representando la condición tal 
     * cual se ejecutará en la base de datos, o puede ser un array de filtros 
     * de búsqueda en el que cada filtro es un array que puede ser tanto 
     * asociativo (con los índices: 'campo' que representa el nombre del campo, 
     * 'filtro' que representa el tipo de filtro espicificado como constante de 
     * esta clase, 'valor' que representa el valor de comparación de la 
     * búsqueda) o indexado con el orden (campor, filtro, valor). Ejemplo de su 
     * uso:
     * #Ejemplo filtro por apellido terminado en 'ez' y a la vez que tenga entre
     * 21 y 35 años:
     *  array(
     *      array(
     *          'campo' => 'apellido',
     *          'filtro' => Conexion::FILTRO_BUSQUEDA_COINCIDIR_STRING_DERECHO,
     *          'valor' => 'ez'
     *          ),
     *      array(
     *          'edad',
     *          Conexion::FILTRO_BUSQUEDA_MAYOR_QUE,
     *          20
     *          ),
     *      array(
     *          'edad',
     *          Conexion::FILTRO_BUSQUEDA_MENOR_QUE,
     *          36
     *          )
     *  );
     * @return boolean Devuelve true si tuvo éxito al actualizar los registros 
     * y false en caso contrario.
     */
    public function actualizar($tabla, $datos, $filtros = null) {
        $this->autoconectar();
        $actualizacion = "UPDATE {$tabla} SET ";
        $lista_seteos = "";
        foreach ($datos as $nombre_campo => $valor_campo) {
            if (is_null($valor_campo) || (is_string($valor_campo) && $valor_campo === '') || (is_int($valor_campo) && $valor_campo === -1)) {
                //$lista_seteos .= " NULL, ";
            } else {
                $lista_seteos .= $nombre_campo . " = ";
                if (is_numeric($valor_campo)) {
                    $lista_seteos .= "{$valor_campo}, ";
                } else {
                    $lista_seteos .= "'{$this->_conexion->real_escape_string($valor_campo)}', ";
                }
            }
        }
        $actualizacion .= rtrim($lista_seteos, ", ");
        if (!is_null($filtros)) {
            $actualizacion .= " WHERE 1=1 ";
            if (is_array($filtros)) {
                $actualizacion .= $this->filtrar_resultados($filtros);
            } else {
                //si es un string, lo anexo como viene
                $actualizacion .= " AND {$filtros}";
            }
        }
        return $this->actualizar_simple($actualizacion);
    }

    /**
     * Realiza una actualización de un único registro para la tabla indicada, 
     * con el conjunto de campos a ser modificados pasados como parámetro.
     * @param string $tabla Nombre de la tabla de la cual se hará la 
     * modificación.
     * @param array $datos Array asociativo donde la clave es el nombre del 
     * campo y el valor es el nuevo valor a obtener en dicho campo.
     * @param string $campo_unico Nombre del campo cuyo valor se supone único.
     * @param mixed $valor_unico Valor único buscado. Puede ser un entero, un 
     * string, etc.
     * @return boolean Devuelve true si tuvo éxito al actualizar los registros 
     * y false en caso contrario.
     */
    public function actualizar_unico($tabla, $datos, $campo_unico, $valor_unico) {
        $this->autoconectar();
        $filtro = array('campo' => $campo_unico, 'filtro' => self::FILTRO_BUSQUEDA_IGUALDAD, 'valor' => $valor_unico);
        return $this->actualizar($tabla, $datos, array($filtro));
    }

    /**
     * Ejecuta una sentencia UPDATE  pasada como parámetro para actualizar la 
     * base de datos. Si tiene éxito, setea la cantidad de filas afectadas.
     * @param string $actualizacion Sentencia a ser ejecutada para la 
     * actulización de los datos.
     * @return boolean Devuelve true si tuvo éxito al actualizar los registros 
     * y false en caso contrario.
     */
    public function actualizar_simple($actualizacion) {
        $this->autoconectar();
        $this->_cantidad_filas_afectadas = Conexion::VALOR_INVALIDO_CANTIDAD_FILAS_AFECTADAS;
        if (!$this->_conexion->query($actualizacion)) {
            $this->error("Error al ejecutar actualización");
            return false;
        }
        $this->_cantidad_filas_afectadas = $this->_conexion->affected_rows;
        if ($this->_modo_autoconexion) {
            $this->cerrar();
        }
        return true;
    }

    public function consulta_devolvio_registros() {
        return $this->_cantidad_filas_obtenidas > 0;
    }

    public function exito_consulta() {
        return $this->_cantidad_filas_obtenidas != Conexion::VALOR_INVALIDO_CANTIDAD_FILAS_OBTENIDAS;
    }

    public function exito_insercion() {
        return $this->_id_insercion != Conexion::VALOR_INVALIDO_ID_INSERCION;
    }

    public function exito_actualizacion() {
        return $this->_cantidad_filas_afectadas != Conexion::VALOR_INVALIDO_CANTIDAD_FILAS_AFECTADAS;
    }

    public function llamar_procedimiento_almacenado($procedimiento, $tipos, $parametros) {
        $procedimiento .= '(';
        if (count($parametros) > 0) {
            for ($i = 0; $i < count($parametros) - 1; $i++) {
                $procedimiento .= "?, ";
            }
            $procedimiento .= "?";
        }
        $procedimiento .= ')';
        $this->autoconectar();
        $sp = $this->_conexion->prepare("CALL $procedimiento");
        //$tipos = implode('', array_keys($parametros));
        //$argumentos = array_values($parametros);
        $argumentos = $parametros;
        array_unshift($argumentos, $tipos);
        call_user_func_array(array($sp, 'bind_param'), $argumentos);
        $sp->execute();
        $conjunto_resultados = $sp->get_result();
        $resultado = $this->_armar_resultado($conjunto_resultados);
        if ($this->_modo_autoconexion) {
            $this->cerrar();
        }
        return $resultado;
    }

    /**
     * 
     * @param mysqli_result $conjunto_resultados
     * @return false | array
     */
    private function _armar_resultado($conjunto_resultados) {
        $this->_id_insercion = Conexion::VALOR_INVALIDO_ID_INSERCION;
        $this->_cantidad_filas_afectadas = Conexion::VALOR_INVALIDO_CANTIDAD_FILAS_AFECTADAS;
        $this->_cantidad_filas_obtenidas = Conexion::VALOR_INVALIDO_CANTIDAD_FILAS_OBTENIDAS;
        $resultado = array();
        if ($conjunto_resultados === false) {
            //$this->error("Error al ejecutar el procedimiento almacenado");
            return false;
        } else {
            if ($this->_conexion->insert_id !== false) {
                $this->_id_insercion = $this->_conexion->insert_id;
            }
            if ($this->_conexion->affected_rows !== false) {
                $this->_cantidad_filas_afectadas = $this->_conexion->affected_rows;
            }
            if (isset($conjunto_resultados->num_rows) && $conjunto_resultados->num_rows !== false) {
                $this->_cantidad_filas_obtenidas = $conjunto_resultados->num_rows;
                for ($i = 0; $i < $conjunto_resultados->num_rows; $i++) {
                    $resultado[] = $conjunto_resultados->fetch_assoc();
                }
                $conjunto_resultados->close();
            }
        }
        return $resultado;
    }

    /**
     * Devuelve un conjunto de resultados.
     * Si el procedimiento no devuelve nada entonces devuelve un conjunto
     * vacío.
     * Retorna false en caso que haya ocurrido un error.
     * @param string $procedimiento
     * @return matriz de resultados.
     */
    public function llamar_procedimiento_almacenado_simple($procedimiento) {
        $this->autoconectar();
        $conjunto_resultados = $this->_conexion->query("CALL {$procedimiento}");
        $resultado = $this->_armar_resultado($conjunto_resultados);
        if ($this->_modo_autoconexion) {
            $this->cerrar();
        }
        return $resultado;
    }

    /**
     * Comienza una transacción. Para esto sigue una serie de pasos: 1) Setea 
     * el modo de autoconexión en false para impedir que cada comando que se 
     * llame (como ser inserción, actualización etc.) intente conectarse con la 
     * base de datos antes de ejecutar su consulta e intente cerrar la conexión 
     * al finalizar la misma (comportamiento por defecto, pues modo de 
     * autoconexión está en true por defecto). 2) Intenta conectarse con la 
     * base de datos (por lo que requiere que el método estático init se haya 
     * llamado previamente para poder tener disponibles los parámetros de 
     * conexión). 3) Intenta setear el modo de trabajo de MySQL a autocommit = 
     * false, así todas las consultas a ser ejecutadas nos son implícitamente 
     * confirmadas tras ejecutarse. 4) Intenta comenzar la transacción 
     * propiamente dicha. Si alguna de estas acciones falla, devuele false.
     * @return boolean Devuelve true si puede conectarse, setear el modo de 
     * autocommit a false, y comenzar la transacción, devuelve false en case de 
     * no tener éxito.
     */
    public function transaccion_comenzar() {
        if (!$this->conectar() || !$this->_conexion->autocommit(false) || !$this->_conexion->begin_transaction()) {
            $this->error("Error al comenzar la transacción");
            return false;
        }
        $this->set_modo_autoconexion(false);
        return true;
    }

    /**
     * Trata de confirmar o revertir los cambios de las consultas efectuadas 
     * a la base de datos, tenga o no éxito, reseteará el modo de trabajo de 
     * MySQL a autocommit = true y cerrará la conexición con la base de datos y 
     * al mismo tiempo seteará el modo de autoconexión a true (valor por 
     * defecto) para permitir conectarse y cerrar la conexión implícitamente al 
     * ejecutar las consultas.
     * @return boolean Devuelve true si tuvo éxito en confirmar la transacción 
     * y si no surgió ningún problema al intentar hacerlo, false en caso 
     * contrario.
     */
    public function transaccion_terminar($transaccion_exitosa) {
        if ($transaccion_exitosa == true) {
            //commit
            return $this->transaccion_confirmar();
        } else {
            //rollback
            return $this->transaccion_revertir();
        }
    }

    /**
     * Trata de confirmar los cambios de las consultas efectuadas a la base de 
     * datos, tenga o no éxito, reseteará el modo de trabajo de MySQL a 
     * autocommit = true y cerrará la conexición con la base de datos y al 
     * mismo tiempo seteará el modo de autoconexión a true (valor por defecto) 
     * para permitir conectarse y cerrar la conexión implícitamente al ejecutar 
     * las consultas.
     * @return boolean Devuelve true si tuvo éxito en confirmar la transacción 
     * y si no surgió ningún problema al intentar hacerlo, false en caso 
     * contrario.
     */
    public function transaccion_confirmar() {
        $exito = true;
        if (!$this->_conexion->commit()) {
            $this->error("Error al tratar de confirmar la transacción");
            $exito = false;
        }
        if (!$this->_conexion->autocommit(true)) {
            $this->error("Error al finalizar la transacción");
            $exito = false;
        }
        $this->cerrar();
        $this->set_modo_autoconexion(true);
        return $exito;
    }

    /**
     * Trata de revertir los cambios de las consultas efectuadas a la base de 
     * datos, tenga o no éxito, reseteará el modo de trabajo de MySQL a 
     * autocommit = true y cerrará la conexición con la base de datos y al 
     * mismo tiempo seteará el modo de autoconexión a true (valor por defecto) 
     * para permitir conectarse y cerrar la conexión implícitamente al ejecutar 
     * las consultas.
     * @return boolean Devuelve true si tuvo éxito en revertir la transacción 
     * y si no surgió ningún problema al intentar hacerlo, false en caso 
     * contrario.
     */
    public function transaccion_revertir() {
        $exito = true;
        if (!$this->_conexion->rollback()) {
            $this->error("Error al tratar de revertir la transacción");
            $exito = false;
        }
        if (!$this->_conexion->autocommit(true)) {
            $this->error("Error al finalizar la transacción");
            $exito = false;
        }
        $this->cerrar();
        $this->set_modo_autoconexion(true);
        return $exito;
    }

    private function filtrar_resultados($filtros) {
        $this->autoconectar();
        $consulta = "";
        for ($i = 0; $i < count($filtros); $i++) {
            if (isset($filtros[$i]['campo'])) {
                $nombre_campo = $filtros[$i]['campo'];
                $tipo_filtro = $filtros[$i]['filtro'];
                $valor_campo = $filtros[$i]['valor'];
            } else {
                $nombre_campo = $filtros[$i][0];
                $tipo_filtro = $filtros[$i][1];
                $valor_campo = $filtros[$i][2];
            }
            $consulta .= " AND {$nombre_campo} ";
            switch ($tipo_filtro) {
                case(self::FILTRO_BUSQUEDA_COINCIDIR_STRING_COMPLETO)://coincidencia de string por los dos lados
                    $consulta .= " LIKE '%{$this->_conexion->real_escape_string($valor_campo)}%'";
                    break;
                case(self::FILTRO_BUSQUEDA_COINCIDIR_STRING_DERECHO)://coincidencia de string por el lado derecho
                    $consulta .= " LIKE '%{$this->_conexion->real_escape_string($valor_campo)}'";
                    break;
                case(self::FILTRO_BUSQUEDA_COINCIDIR_STRING_IZQUIERDO)://coincidencia de string por el lado izquierdo
                    $consulta .= " LIKE '{$this->_conexion->real_escape_string($valor_campo)}%'";
                    break;
                case(self::FILTRO_BUSQUEDA_IGUALDAD)://coincidencia de igualdad
                    $consulta .= " = {$valor_campo}";
                    break;
                case(self::FILTRO_BUSQUEDA_MAYOR_QUE)://coincidencia de "mayor a"
                    $consulta .= " > {$valor_campo}";
                    break;
                case(self::FILTRO_BUSQUEDA_MENOR_QUE)://coincidencia de "menor a"
                    $consulta .= " < {$valor_campo}";
                    break;
                default://otros tipos de filtro
                    $consulta .= " {$valor_campo}";
                    break;
            }
        }
        return $consulta;
    }

    private function error($mensaje = 'Error', $codigo_error = '', $mensaje_error = '') {
        if ($codigo_error === '') {
            $codigo_error = $this->_conexion->errno;
        }
        if ($mensaje_error === '') {
            $mensaje_error = $this->_conexion->error;
        }
        if ($this->_modo_desarrollo) {
            $this->_error[] = $mensaje . ': (' . $codigo_error . '): ' . $mensaje_error . PHP_EOL;
        } else {
            $this->_error[] = $mensaje . PHP_EOL;
        }
    }

    public function autoconectar() {
        if ($this->_modo_autoconexion === true && is_null($this->_conexion)) {
            $this->conectar();
        }
    }

    public function set_modo_autoconexion($_modo_autoconexion) {
        $this->_modo_autoconexion = $_modo_autoconexion;
    }

    public function get_mysqli() {
        return $this->_conexion;
    }

    public function get_id_insercion() {
        return $this->_id_insercion;
    }

    public function get_cantidad_filas_afectadas() {
        return $this->_cantidad_filas_afectadas;
    }

    function get_cantidad_filas_obtenidas() {
        return $this->_cantidad_filas_obtenidas;
    }

    public function get_error() {
        return $this->_error;
    }

    /**
     * Verifica si ha surgido algún error durante la conexión.
     * @return boolean Devuelvo true si no ha sucedido ningún error, false en 
     * caso contrario.
     */
    public function ok() {
        return empty($this->_error);
    }

    /**
     * Devuelve los errores registrados en forma de string y los separa con el 
     * caracter especificado como parámetro (por defecto es con coma).
     * @param string $separar_por Cadena usada como separador de los errores.
     * @return string Devuelve el conjunto de errores registrados separados con 
     * coma (caracter separador por defecto).
     */
    public function errores($separar_por = ", ") {
        return implode($separar_por, $this->_error);
    }

    /**
     * Devuelve un string con los parámetros de conexión seteados en el método 
     * estático init.
     * @return string Cadena con los parámetros de conexión.
     */
    public static function info() {
        $conexion_actual = "host: " . $this->_host . PHP_EOL
                . "usuario: " . $this->_nombre_usuario . PHP_EOL
                . "base datos: " . $this->_base_datos . PHP_EOL;
        return $conexion_actual;
    }

    /**
     * Verifica si está abierta la conexiñon actual con la base de datos 
     * especificada en el método estático init.
     * @return boolean Devuelve true si existe una conexión válida con las base 
     * de datos, false en caso contrario.
     */
    public function esta_conectado() {
        return !is_null($this->_conexion);
    }

}
