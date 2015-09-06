<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Nos Juntemos!</title>
        <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
        <link href="<?php echo $RUTA_WEB; ?>/css/general.css" type="text/css" rel="stylesheet"/>
        <link href="<?php echo $RUTA_WEB; ?>/css/crear_reunion.css" type="text/css" rel="stylesheet"/>
        <script src="<?php echo $RUTA_WEB; ?>/js/jquery.js" type="text/javascript"></script>
        <script src="<?php echo $RUTA_WEB; ?>/js/crear_reunion.js" type="text/javascript"></script>
    </head>
    <body>
        <main>
            <h4>Crear nueva reunión</h4>
            <form action="<?php echo $RUTA_WEB; ?>/src/ctrl/crear_reunion.ctrl.php" method="post" >
                <table>
                    <tr>
                        <td>
                            Nombre de reunión
                        </td>
                        <td>
                            <input type="text" name="txt_nombre" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Lugar
                        </td>
                        <td>
                            <input type="text" name="txt_lugar" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Fecha (semana)
                        </td>
                        <td>
                            <input type="text" name="txt_fecha" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Días
                        </td>
                        <td>
                            <ul id="lista_dias">
                                <?php foreach ($tmpl_dias as $tmpl_dia): ?>
                                    <li>
                                        <input type="checkbox" name="chk_dia_<?php echo $tmpl_dia["id"]; ?>" /><label><?php echo $tmpl_dia["nombre"]; ?></label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Hora de inicio
                        </td>
                        <td>
                            <input type="text" name="txt_hora_inicio" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Hora de fin
                        </td>
                        <td>
                            <input type="text" name="txt_hora_fin" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                        </td>
                        <td>
                            <input type="submit" name="btn_crear" value="Crear reunión" class="boton botonAcero" />
                        </td>
                    </tr>
                </table>
            </form>
        </main>
    </body>
</html>
