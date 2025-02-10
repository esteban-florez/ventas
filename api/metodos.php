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
	case 'registrar':
        $resultado = registrarMetodo($payload->metodo);
		echo json_encode($resultado);
		break;

	case 'editar':
        $resultado = editarMetodo($payload->metodo);
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

	
	default:
		echo json_encode("No se reconoce");
		break;
}
