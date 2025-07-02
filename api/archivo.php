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

    $url = $_ENV['NOTIFS_URL'] . "/pdf?numero={$numero}";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['pdf' => $base64]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Origin: ' . $_ENV['WEB_URL'],
        'X-Api-Key: ' . $_ENV['NOTIFS_API_KEY']
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return $data;
} catch (\Throwable $th) {
    dd($th->getMessage());
    return ['mensaje' => 'Error processing PDF'];
}


