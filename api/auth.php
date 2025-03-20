<?php

['permisos' => $permisos, 'usuario' => $usuario] = obtenerEstadoAutenticacion();

$arr = explode(DIRECTORY_SEPARATOR, $_SERVER['SCRIPT_FILENAME']);
$filename = str_replace('php', '', array_pop($arr));

$accion = $filename.$payload->accion;

$permisos = json_decode($permisos);

if ($permisos->$accion === false) {
    dd($accion);
    http_response_code(401);
    echo json_encode([
        'mensaje' => 'No se encuentra autorizado para hacer esta petición',
    ]);
    exit;
}
