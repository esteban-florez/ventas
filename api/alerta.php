<?php

function alertaNuevoProducto()
{
    try {
        $id = obtenerUltimoId('productos');
        $producto = obtenerProductoPorId($id);
        $url = $_ENV['NOTIFS_URL'] . "/producto";

        $clientes = selectQuery("SELECT telefono
            FROM clientes
            WHERE telefono != ''
            AND telefono IS NOT NULL
        ");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            json_encode(['producto' => $producto, 'clientes' => $clientes])
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Origin: ' . $_ENV['WEB_URL'],
            'X-Api-Key: ' . $_ENV['NOTIFS_API_KEY'],
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        dd($data);
        return $data;
    } catch (\Throwable $th) {
        dd($th->getMessage());
        return ['mensaje' => 'Error en alertaNuevoProducto: ' . $th->getMessage()];
    }
}