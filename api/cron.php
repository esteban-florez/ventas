<?php

if (php_sapi_name() !== 'cli')
    die('Not allowed');

require_once 'funciones.php';
require_once 'encabezado.php';

$url = $_ENV['NOTIFS_URL'] . '/cuentas';

$filtros = new stdClass;
$filtros->fechaInicio = null;
$filtros->fechaFin = null;

$cuentas = obtenerCuentasApartados($filtros, 'cuenta');

try {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt(
        $ch,
        CURLOPT_POSTFIELDS,
        json_encode(['cuentas' => $cuentas])
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Origin: ' . $_ENV['WEB_URL'],
        'X-Api-Key: ' . $_ENV['NOTIFS_API_KEY'],
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new \Exception(curl_error($ch));
    }

    curl_close($ch);

    $data = json_decode($response, true);

} catch (\Throwable $th) {
    dd($th->getMessage());
}