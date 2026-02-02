<?php

require_once __DIR__ . '/encabezado.php';
require_once __DIR__ . '/funciones.php';

$apiUrl = 'https://ve.dolarapi.com/v1/dolares';

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_CONNECTTIMEOUT => 5,
  CURLOPT_TIMEOUT => 10,
  CURLOPT_SSL_VERIFYPEER => true,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode !== 200) {
  http_response_code(502);
  echo json_encode(['success' => false, 'error' => 'Failed to fetch API', 'details' => $curlErr]);
  exit;
}

$data = json_decode($response, true);
$promedio = null;
if (is_array($data) && isset($data[0]['promedio'])) {
  $promedio = $data[0]['promedio'];
}

if (!is_numeric($promedio)) {
  http_response_code(422);
  echo json_encode(['success' => false, 'error' => 'promedio not found or invalid in API response']);
  exit;
}

$promedio = (float) $promedio;

$insertResult = insertarTasa($promedio);

if (is_int($insertResult) && $insertResult > 0) {
  echo json_encode(['success' => true, 'valor' => $promedio, 'id' => $insertResult]);
} else {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Database error', 'details' => $insertResult]);
}