<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Nos Juntemos!</title>
        <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
        <link href="<?php echo $RUTA_WEB; ?>/css/general.css" type="text/css" rel="stylesheet"/>
        <link href="<?php echo $RUTA_WEB; ?>/css/consultar_reunion.css" type="text/css" rel="stylesheet"/>
        <link href="<?php echo $RUTA_WEB; ?>/css/scheduler.css" type="text/css" rel="stylesheet"/>
        <script src="<?php echo $RUTA_WEB; ?>/js/jquery.js" type="text/javascript"></script>
        <script src="<?php echo $RUTA_WEB; ?>/js/scheduler.js" type="text/javascript"></script>
        <script src="<?php echo $RUTA_WEB; ?>/js/asistir.js" type="text/javascript"></script>
    </head>
    <body>
        <main>
            <?php if (isset($tmpl_hash)): ?>
                <span id="idReunion"><?php echo $tmpl_hash; ?></span>
                <h4 id="nombre"></h4>
                <table id="datosGenerales">
                    <tr>
                        <td>
                            Lugar:
                        </td>
                        <td>
                            <span id="lugar"></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Fecha:
                        </td>
                        <td>
                            <span id="fecha"></span>       
                        </td>
                    </tr>
                </table>
                <br/>
                <form>
                    <table>
                        <tr>
                            <td>
                                Tu nombre:
                            </td>
                            <td>
                                <input type='text' id="txtNombre" />
                            </td>
                            <td>
                                <button type="button" id="btnAsistir" class="boton botonAcero">Asistir</button>
                            </td>
                        </tr>
                    </table>
                    <div id="mensaje" class="mensaje"></div>
                </form>
                <div>
                    Marca tus horarios
                </div>
                <table id="tabla_horario">
                </table>
            <?php else: ?>
                No existe tan reunión...
            <?php endif; ?>
        </main>
    </body>
</html>
