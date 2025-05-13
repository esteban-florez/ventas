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
		echo json_encode(obtenerChoferes());
		break;
	
	case 'obtener_por_nombre':
		echo json_encode(obtenerChoferesPorNombre($payload->nombre));
		break;

	case 'obtener_por_id':
		echo json_encode(obtenerChoferPorId($payload->id));
		break;

	case 'editar':
		echo json_encode(editarChofer($payload->chofer));
		break;
	
    case 'pagar_chofer':
        echo json_encode(registrarPagoChofer($payload->pago));
        break;

    case 'registrar':
        echo json_encode(registrarChofer($payload->chofer));
        break;

	default:
		echo json_encode("No se reconoce");
		break;
}
