<?php

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

$response = fetch()
    ->baseUri($url)
    ->withHeaders(['Content-Type' => 'application/json', 'Origin' => $_ENV['WEB_URL']])
    ->withBody(['cuentas' => $cuentas])
    ->post('/cuentas');

$data = $response->json();

echo json_encode([
    'res' => $data,
    'cuentas' => $cuentas,
]);
