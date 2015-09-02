<?php

require_once '../../config.php';

$tmpl_hash = filter_input(INPUT_GET, "id");

require_once $RUTA_SERVIDOR . '/tmpl/consultar_reunion.tmpl.php';