<?php

['permisos' => $permisos, 'usuario' => $usuario] = obtenerEstadoAutenticacion();

$arr = explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']);
$filename = str_replace('php', '', array_pop($arr));

$accion = $filename.$payload->accion;

$permisos = json_decode($permisos);

$noUsuario = !$usuario;
$noAutorizado = $permisos->$accion === false;

if ($accion !== 'usuarios.iniciar_sesion' && ($noUsuario || $noAutorizado)) {
    http_response_code(401);
    echo json_encode([
        'mensaje' => 'No se encuentra autorizado para hacer esta petición',
    ]);
    exit;
}
