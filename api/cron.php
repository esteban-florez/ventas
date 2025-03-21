<?php

if (php_sapi_name() !== 'cli') die('Not allowed');

require_once 'funciones.php';
require_once 'encabezado.php';

$scheme = $_ENV['NOTIFS_SCHEME'];
$host = $_ENV['NOTIFS_HOST'];
$port = $_ENV['NOTIFS_PORT'];

$url = "$scheme://$host:$port";

$filtros = new stdClass;
$filtros->fechaInicio = null;
$filtros->fechaFin = null;

$cuentas = obtenerCuentasApartados($filtros, 'cuenta');

try {
    $response = fetch()
        ->baseUri($url)
        ->withHeaders([
            'Content-Type' => 'application/json',
            'Origin' => $_ENV['WEB_URL'],
            'X-Api-Key' => $_ENV['NOTIFS_API_KEY'],
        ])
        ->withBody(['cuentas' => $cuentas])
        ->post('/cuentas');
} catch (\Throwable $th) {
    dd($th->getMessage());
}
