<?php

require_once '../../config.php';

$tmpl_hash = $_REQUEST["id"];

require_once $RUTA_SERVIDOR . '/tmpl/consultar_reunion.tmpl.php';