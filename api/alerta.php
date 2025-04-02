<?php

function alertaNuevoProducto() {
    try {
        $id = obtenerUltimoId('productos');
        $producto = obtenerProductoPorId($id);
        $url = $_ENV['NOTIFS_URL'];
    
        $clientes = selectQuery("SELECT telefono
            FROM clientes
            WHERE telefono != ''
            AND telefono IS NOT NULL
        ");
    
        $response = fetch()
            ->baseUri($url)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Origin' => $_ENV['WEB_URL'],
                'X-Api-Key' => $_ENV['NOTIFS_API_KEY'],
            ])
            ->withBody(['producto' => $producto, 'clientes' => $clientes])
            ->post('/producto');
    
        $data = $response->json();

        dd($data);
        return $data;
    } catch (\Throwable $th) {
        dd($th->getMessage());
        return ['mensaje' => 'Error en fetch-php: '.$th->getMessage()];
    }
}
