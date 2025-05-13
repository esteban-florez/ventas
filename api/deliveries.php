<?php

include_once "encabezado.php";
$payload = json_decode(file_get_contents("php://input"));
if (!$payload) {
	http_response_code(500);
	exit;
}

include_once "funciones.php";
include_once "auth.php";

$accion = $payload->accion;

switch ($accion) {
	case 'obtener':
		echo json_encode(obtenerDeliveries($payload->filtros));
		break;

	default: 
		echo json_encode("No se reconoce");
		break;
}
