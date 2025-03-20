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
	case 'registrar':
        $resultado = registrarMetodo($payload->metodo);
		echo json_encode($resultado);
		break;

	case 'editar':
        $resultado = editarMetodo($payload->id, $payload->metodo);
		echo json_encode($resultado);
		break;

	case 'eliminar':
        $resultado = eliminarMetodo($payload->id);
		echo json_encode($resultado);
		break;

	case 'obtener':
		$metodos = obtenerMetodos();
		echo json_encode($metodos);
		break;

    case 'obtener_por_id':
        echo json_encode(obtenerMetodoPorId($payload->id));
        break;
	
	default:
		echo json_encode("No se reconoce");
		break;
}
