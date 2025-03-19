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
		echo json_encode(registrarUsuario($payload->usuario));
		break;

	case 'obtener':
		echo json_encode(obtenerUsuarios());
		break;

	case 'iniciar_sesion':
		echo json_encode(iniciarSesion($payload->usuario));
		break;

	case 'obtener_por_id':
		echo json_encode(obtenerUsuarioPorId($payload->id));
		break;

	case 'editar':
		echo json_encode(editarUsuario($payload->usuario));
		break;
	
	case 'eliminar':
		echo json_encode(eliminarUsuario($payload->id));
		break;
	
	case 'verificar_password':
		echo json_encode(verificarPassword($payload->idUsuario, $payload->password));
		break;

	case 'cambiar_password':
		echo json_encode(cambiarPassword($payload->idUsuario, $password));
		break;

    case 'estado_autenticacion':
        echo json_encode(obtenerEstadoAutenticacion());
        break;

    case 'cerrar_sesion':
        echo json_encode(cerrarSesion());
        break;
	
	default:
		echo json_encode("No se reconoce");
		break;
}
