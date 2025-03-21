<?php

include_once 'encabezado.php';
include_once 'funciones.php';
include_once 'auth.php';

try {
    $numero = $_GET['numero'];
    $pdf = $_FILES['pdf'];
    $path = $pdf['tmp_name'];

    $contents = file_get_contents($path);
    $base64 = base64_encode($contents);

    $url = $_ENV['NOTIFS_URL'];
    $response = fetch()
        ->baseUri($url)
        ->withHeaders([
            'Content-Type' => 'application/json',
            'Origin' => $_ENV['WEB_URL'],
            'X-Api-Key' => $_ENV['NOTIFS_API_KEY'],
        ])
        ->withBody(['pdf' => $base64])
        ->post("/pdf?numero={$numero}");

    $data = $response->json();
    echo json_encode($data);
} catch (\Throwable $th) {
    dd($th->getMessage());
    echo json_encode(['mensaje' => 'Error en fetch-php']);
}


