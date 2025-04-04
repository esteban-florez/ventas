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
		echo json_encode(obtenerProveedores());
		break;

	case 'obtener_por_id':
		echo json_encode(obtenerProveedorPorId($payload->id));
		break;

	case 'editar':
		echo json_encode(editarProveedor($payload->proveedor));
		break;

    case 'registrar':
        echo json_encode(registrarProveedor($payload->proveedor));
        break;

    case 'obtener_pagos':
        echo json_encode(obtenerPagosProveedor($payload->id));
        break;

    case 'pagar_proveedor':
        echo json_encode(pagarProveedor($payload->pago));
        break;
	
	default:
		echo json_encode("No se reconoce");
		break;
}
