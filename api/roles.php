<?php

include_once "encabezado.php";
$payload = json_decode(file_get_contents("php://input"));
if (!$payload) {
    http_response_code(500);
    exit;
}

include_once "funciones.php";

$accion = $payload->accion;

switch ($accion) {
	case 'obtener':
		echo json_encode(obtenerRoles());
		break;

	case 'obtener_por_id':
		echo json_encode(obtenerRolPorId($payload->id));
		break;

	case 'editar':
		echo json_encode(editarRol($payload->rol));
		break;

    case 'registrar':
        echo json_encode(registrarRol($payload->rol));
        break;
	
	default:
		echo json_encode("No se reconoce");
		break;
}
