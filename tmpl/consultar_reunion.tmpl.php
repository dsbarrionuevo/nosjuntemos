<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Nos Juntemos!</title>
        <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
        <link href="<?php echo $RUTA_WEB; ?>/css/index.css" type="text/css" rel="stylesheet"/>
        <link href="<?php echo $RUTA_WEB; ?>/css/horario.css" type="text/css" rel="stylesheet"/>
        <script src="<?php echo $RUTA_WEB; ?>/js/jquery.js" type="text/javascript"></script>
        <script src="<?php echo $RUTA_WEB; ?>/js/horario.js" type="text/javascript"></script>
        <script src="<?php echo $RUTA_WEB; ?>/js/consultar_reunion.js" type="text/javascript"></script>
    </head>
    <body>
        <main>
            <span id="hash"><?php echo $tmpl_hash; ?></span>
            <h2 id="nombre"></h2>
            <h4 id="lugar"></h4>
            <table id="tabla_horario">
            </table>
        </main>
    </body>
</html>
