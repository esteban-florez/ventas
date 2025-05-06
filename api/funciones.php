<?php
/*

 _______      ___  __   __  _______  _______  _______  _______ 
|   _   |    |   ||  | |  ||       ||       ||       ||       |
|  |_|  |    |   ||  | |  ||  _____||_     _||    ___||  _____|
|       |    |   ||  |_|  || |_____   |   |  |   |___ | |_____ 
|       | ___|   ||       ||_____  |  |   |  |    ___||_____  |
|   _   ||       ||       | _____| |  |   |  |   |___  _____| |
|__| |__||_______||_______||_______|  |___|  |_______||_______|


*/

date_default_timezone_set('America/Caracas');

error_reporting(0);

function obtenerMetodos()
{
    $sentencia = "SELECT * FROM metodos";
    return selectQuery($sentencia);
}

function obtenerMetodoPorId($id)
{
    $sentencia = "SELECT * FROM metodos WHERE id = ?";
    return selectRegresandoObjeto($sentencia, [$id]);
}

function eliminarMetodo($id)
{
    $sentencia = "DELETE FROM metodos WHERE id = ?";
    return eliminar($sentencia, $id);
}

function registrarMetodo($metodo)
{
    $campos = '';
    $marcadores = '';

    switch ($metodo->tipo) {
        case 'Pago Móvil':
            $campos = 'telefono, banco, tipoCi, ci';
            $marcadores = '?,?,?,?';
            $datos = [$metodo->telefono, $metodo->banco, $metodo->tipoCi, $metodo->ci];
            break;

        case 'Transferencia':
            $campos = 'cuenta, banco, tipoCi, ci, beneficiario';
            $marcadores = '?,?,?,?,?';
            $datos = [$metodo->cuenta, $metodo->banco, $metodo->tipoCi, $metodo->ci, $metodo->beneficiario];
            break;

        default:
            $campos = 'correo';
            $marcadores = '?';
            $datos = [$metodo->correo];
            break;
    }

    $parametros = array_merge([$metodo->nombre], $datos);
    $sentencia = "INSERT INTO metodos (nombre, $campos) VALUES (?,$marcadores)";

    return insertar($sentencia, clean($parametros));
}

function editarMetodo($id, $metodo)
{
    $sentencia = 'UPDATE metodos SET nombre = ?, cuenta = ?, banco = ?, tipoCi = ?, ci = ?, beneficiario = ?, telefono = ?, correo = ? WHERE id = ?';

    $parametros = [$metodo->nombre, $metodo->cuenta, $metodo->banco, $metodo->tipoCi, $metodo->ci, $metodo->beneficiario, $metodo->telefono, $metodo->correo, $id];

    return editar($sentencia, $parametros);
}

/*

 __   __  _______  __    _  _______  _______  _______ 
|  | |  ||       ||  |  | ||       ||   _   ||       |
|  |_|  ||    ___||   |_| ||_     _||  |_|  ||  _____|
|       ||   |___ |       |  |   |  |       || |_____ 
|       ||    ___||  _    |  |   |  |       ||_____  |
 |     | |   |___ | | |   |  |   |  |   _   | _____| |
  |___|  |_______||_|  |__|  |___|  |__| |__||_______|

*/

function obtenerVentasPorDiaMes($mes, $anio)
{
    $sentencia = "SELECT DATE_FORMAT(fecha, '%Y-%m-%d') AS dia, IFNULL(SUM(total), 0) AS totalVentas FROM ventas 
	WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
	GROUP BY dia";
    return selectPrepare($sentencia, [$mes, $anio]);
}

function obtenerTotalesVentasPorMes($anio)
{
    $sentencia = "SELECT MONTH(fecha) AS mes, IFNULL(SUM(total), 0) AS totalVentas FROM ventas 
        WHERE YEAR(fecha) = ? 
        GROUP BY MONTH(fecha) ORDER BY mes ASC";
    return selectPrepare($sentencia, [$anio]);
}

function calcularTotalIngresos()
{
    $sentencia = "SELECT (SELECT IFNULL(SUM(total), 0) FROM ventas) + (SELECT IFNULL(SUM(monto), 0) FROM abonos) AS totalIngresos";
    return selectRegresandoObjeto($sentencia)->totalIngresos;
}

function calcularTotalIngresosHoy()
{
    $sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE DATE(fecha) = CURDATE()) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE DATE(fecha) = CURDATE()) AS totalIngresos";
    return selectRegresandoObjeto($sentencia)->totalIngresos;
}

function calcularTotalIngresosSemana()
{
    $sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE WEEK(fecha) = WEEK(NOW())) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE WEEK(fecha) = WEEK(NOW())) AS totalIngresos";
    return selectRegresandoObjeto($sentencia)->totalIngresos;
}

function calcularTotalIngresosMes()
{
    $sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())) AS totalIngresos";
    return selectRegresandoObjeto($sentencia)->totalIngresos;
}

function calcularIngresosPendientes()
{
    $sentencia = "SELECT (
        (SELECT IFNULL(SUM(total), 0) FROM cuentas_apartados)
        - (SELECT IFNULL(SUM(monto), 0) FROM abonos)
    ) as pendientes";
    return selectRegresandoObjeto($sentencia)->pendientes;
}

function obtenerCotizaciones($filtros, $tipo)
{
    $sentencia = "SELECT cotizaciones.id, cotizaciones.fecha, cotizaciones.total, cotizaciones.hasta, IFNULL(clientes.nombre, 'MOSTRADOR') AS nombreCliente, IFNULL(usuarios.usuario, 'NO ENCONTRADO') AS nombreUsuario, clientes.telefono AS telefonoCliente, clientes.direccion AS direccionCliente
		FROM cotizaciones
		LEFT JOIN clientes ON clientes.id = cotizaciones.idCliente
		LEFT JOIN usuarios ON usuarios.id = cotizaciones.idUsuario 
		WHERE 1";

    $parametros = [];

    if ($filtros->fechaInicio && $filtros->fechaFin) {
        $sentencia .= " AND (DATE(cotizaciones.fecha) >= ? AND DATE(cotizaciones.fecha) <= ?)";
        array_push($parametros, $filtros->fechaInicio);
        array_push($parametros, $filtros->fechaFin);
    }

    $sentencia .= " ORDER BY cotizaciones.id DESC";

    $cotizaciones = selectPrepare($sentencia, $parametros);
    return agregarProductosVendidos($cotizaciones, $tipo);
}

function eliminarCotizacion($id)
{
    $sentenciaEliminarCotizacion = "DELETE FROM cotizaciones WHERE id = ?";
    $cotizacionEliminada = eliminar($sentenciaEliminarCotizacion, $id);

    $sentenciaEliminarProductos = "DELETE FROM productos_cotizados WHERE idCotizacion = ?";
    $productosEliminados = eliminar($sentenciaEliminarProductos, $id);
    return $cotizacionEliminada && $productosEliminados;
}

function obtenerTotalVentas($filtros)
{
    $sentencia = "SELECT IFNULL(SUM(total), 0) AS totalVentas FROM ventas";
    $parametros = [];
    $tieneWhere = false;

    if ($filtros->clienteId) {
        $sentencia .= " LEFT JOIN clientes ON clientes.id = ventas.idCliente";
        $sentencia .= " WHERE clientes.id = ?";
        array_push($parametros, $filtros->clienteId);
        $tieneWhere = true;
    }

    if ($filtros->fechaInicio && $filtros->fechaFin) {
        $sentencia .= $tieneWhere ? " AND " : " WHERE ";
        $sentencia .= "DATE(ventas.fecha) >= ? AND DATE(ventas.fecha) <= ?";
        array_push($parametros, $filtros->fechaInicio);
        array_push($parametros, $filtros->fechaFin);
    }

    return selectRegresandoObjeto($sentencia, $parametros)->totalVentas;
}

function obtenerTotalCuentasApartados($filtros, $tipo)
{
    $sentencia = "SELECT IFNULL(SUM(total), 0) AS total FROM cuentas_apartados";
    $parametros = [];

    if ($filtros->clienteId) {
        $sentencia .= " LEFT JOIN clientes ON clientes.id = cuentas_apartados.idCliente";
        $sentencia .= " WHERE clientes.id = ?";
        array_push($parametros, $filtros->clienteId);
        $sentencia .= " AND cuentas_apartados.tipo = ?";
    } else {
        $sentencia .= " WHERE cuentas_apartados.tipo = ?";
    }

    array_push($parametros, $tipo);

    if ($filtros->fechaInicio && $filtros->fechaFin) {
        $sentencia .= " AND (DATE(cuentas_apartados.fecha) >= ? AND DATE(cuentas_apartados.fecha) <= ?)";
        array_push($parametros, $filtros->fechaInicio);
        array_push($parametros, $filtros->fechaFin);
    }

    return selectRegresandoObjeto($sentencia, $parametros)->total;
}

function obtenerTotalPorPagarCuentasApartados($filtros, $tipo)
{
    $sentencia = "
    WITH 
        pagos AS (
            SELECT IFNULL(SUM(abonos.monto), 0) AS pagado
                FROM abonos
                    LEFT JOIN cuentas_apartados ON cuentas_apartados.id = abonos.idCuenta
                    LEFT JOIN clientes ON clientes.id = cuentas_apartados.idCliente
            WHERE cuentas_apartados.id IS NOT NULL
                AND cuentas_apartados.tipo = ?
            {condicion_cliente}
            {condicion_fecha}
        ),
        deuda AS (
            SELECT IFNULL(SUM(total), 0) AS total_deuda
                FROM cuentas_apartados
                    LEFT JOIN clientes ON clientes.id = cuentas_apartados.idCliente
            WHERE cuentas_apartados.id IS NOT NULL
                AND cuentas_apartados.tipo = ?
            {condicion_cliente}
            {condicion_fecha}
        )
    SELECT IFNULL((deuda.total_deuda - pagos.pagado), 0) AS por_pagar
    FROM pagos, deuda";

    $parametros = [];

    $condicionCliente = "";
    if (!empty($filtros->clienteId)) {
        $condicionCliente = " AND clientes.id = ?";
        array_push($parametros, $tipo);
        array_push($parametros, $filtros->clienteId);
        array_push($parametros, $tipo);
        array_push($parametros, $filtros->clienteId);
    } else {
        array_push($parametros, $tipo);
        array_push($parametros, $tipo);
    }

    $condicionFecha = "";
    if (!empty($filtros->fechaInicio) && !empty($filtros->fechaFin)) {
        $condicionFecha = " AND (DATE(cuentas_apartados.fecha) >= ? AND DATE(cuentas_apartados.fecha) <= ?)";
        array_push($parametros, $filtros->fechaInicio);
        array_push($parametros, $filtros->fechaFin);
        array_push($parametros, $filtros->fechaInicio);
        array_push($parametros, $filtros->fechaFin);
    }

    $sentencia = str_replace(
        ['{condicion_cliente}', '{condicion_fecha}'],
        [$condicionCliente, $condicionFecha],
        $sentencia
    );

    $resultado = selectRegresandoObjeto($sentencia, $parametros);

    return $resultado->por_pagar;
}

function obtenerPagosCuentasApartados($filtros, $tipo)
{
    $sentencia = "SELECT IFNULL(SUM(abonos.monto), 0) AS totalPagos FROM abonos
        INNER JOIN cuentas_apartados
        ON cuentas_apartados.id = abonos.idCuenta
        AND cuentas_apartados.tipo = ?";

    $parametros = [$tipo];

    if ($filtros->clienteId) {
        $sentencia .= " LEFT JOIN clientes ON clientes.id = cuentas_apartados.idCliente";
        $sentencia .= " WHERE clientes.id = ?";
        array_push($parametros, $filtros->clienteId);
    }

    if ($filtros->fechaInicio && $filtros->fechaFin) {
        $sentencia .= " AND (DATE(cuentas_apartados.fecha) >= ? AND DATE(cuentas_apartados.fecha) <= ?)";
        array_push($parametros, $filtros->fechaInicio);
        array_push($parametros, $filtros->fechaFin);
    }


    return selectRegresandoObjeto($sentencia, $parametros)->totalPagos;
}

function obtenerVentas($filtros)
{
    $sentencia = "SELECT ventas.id, ventas.fecha, ventas.total, ventas.pagado, ventas.simple, ventas.idMetodo, ventas.origen,
        MAX(deliveries.costo) as costoDelivery,
        MAX(deliveries.gratis) as deliveryGratis,
        metodos.nombre as nombreMetodo,
        IFNULL(clientes.nombre, 'MOSTRADOR') AS nombreCliente,
        IFNULL(usuarios.usuario, 'NO ENCONTRADO') AS nombreUsuario,
        deliveries.gratis as deliveryGratis,
        deliveries.idChofer as deliveryId,
        clientes.telefono AS telefonoCliente,
        IFNULL(deliveries.destino, clientes.direccion) AS direccionCliente
        FROM ventas
        LEFT JOIN clientes ON clientes.id = ventas.idCliente
        LEFT JOIN usuarios ON usuarios.id = ventas.idUsuario
        LEFT JOIN metodos ON metodos.id = ventas.idMetodo
        LEFT JOIN deliveries ON deliveries.idVenta = ventas.id";

    $parametros = [];
    if ($filtros->clienteId) {
        $sentencia .= " WHERE clientes.id = ?";
        array_push($parametros, $filtros->clienteId);
    }
    if ($filtros->fechaInicio && $filtros->fechaFin) {
        $sentencia .= ($filtros->clienteId ? " AND " : " WHERE ")
            . " DATE(ventas.fecha) >= ? AND DATE(ventas.fecha) <= ?";
        array_push($parametros, $filtros->fechaInicio);
        array_push($parametros, $filtros->fechaFin);
    }

    $sentencia .= " GROUP BY ventas.id ORDER BY ventas.id DESC";

    $ventas = selectPrepare($sentencia, $parametros);

    foreach ($ventas as $venta) {
        if ($venta->costoDelivery) {
            $venta->delivery = new stdClass;
            $venta->delivery->costo = $venta->costoDelivery;
            $venta->delivery->gratis = $venta->deliveryGratis;
            unset($venta->costoDelivery);
            unset($venta->deliveryGratis);
        }
    }

    return agregarProductosVendidos($ventas, 'venta');
}

function obtenerCuentasApartados($filtros, $tipo)
{
    $sentencia = "SELECT cuentas_apartados.id, cuentas_apartados.fecha,
        cuentas_apartados.tipo, cuentas_apartados.total,
        MAX(deliveries.costo) as costoDelivery, MAX(deliveries.gratis) as deliveryGratis,
        cuentas_apartados.dias, IFNULL(SUM(abonos.monto), 0) AS pagado,
        (cuentas_apartados.total - IFNULL(SUM(abonos.monto), 0)) AS porPagar,
        IFNULL(clientes.nombre, 'MOSTRADOR') AS nombreCliente,
        IFNULL(clientes.telefono, '') AS telefonoCliente,
        deliveries.idChofer as deliveryId,
        clientes.telefono AS telefonoCliente,
        IFNULL(deliveries.destino, clientes.direccion) AS direccionCliente,
        IFNULL(usuarios.usuario, 'NO ENCONTRADO') AS nombreUsuario
        FROM cuentas_apartados
        LEFT JOIN clientes ON clientes.id = cuentas_apartados.idCliente
        LEFT JOIN usuarios ON usuarios.id = cuentas_apartados.idUsuario
        LEFT JOIN abonos ON abonos.idCuenta = cuentas_apartados.id
        LEFT JOIN deliveries ON deliveries.idCuenta = cuentas_apartados.id
        WHERE cuentas_apartados.tipo = ?";

    $parametros = [$tipo];

    if ($filtros->clienteId) {
        $sentencia .= " AND clientes.id = ?";
        array_push($parametros, $filtros->clienteId);
    }
    if ($filtros->fechaInicio && $filtros->fechaFin) {
        $sentencia .= " AND (DATE(cuentas_apartados.fecha) >= ? AND DATE(cuentas_apartados.fecha) <= ?)";
        array_push($parametros, $filtros->fechaInicio);
        array_push($parametros, $filtros->fechaFin);
    }

    $sentencia .= " GROUP BY cuentas_apartados.id ORDER BY cuentas_apartados.id DESC";

    $cuentas = selectPrepare($sentencia, $parametros);

    foreach ($cuentas as $cuenta) {
        if ($cuenta->costoDelivery) {
            $cuenta->delivery = new stdClass;
            $cuenta->delivery->costo = $cuenta->costoDelivery;
            $cuenta->delivery->gratis = $cuenta->deliveryGratis;
            unset($cuenta->costoDelivery);
            unset($cuenta->deliveryGratis);
        }
    }

    return agregarProductosVendidos($cuentas, $tipo);
}

function agregarProductosVendidos($arreglo, $tipo)
{
    if ($tipo === 'cotiza') {
        foreach ($arreglo as $item) {
            $item->productos = obtenerProductosCotizados($item->id);
        }
        return $arreglo;
    }


    foreach ($arreglo as $item) {
        $item->productos = obtenerProductosVendidos($item->id, $tipo);
    }
    return $arreglo;
}

function obtenerProductosVendidos($id, $tipo)
{
    $sentencia = "SELECT productos_vendidos.cantidad, productos_vendidos.precio, productos.nombre, productos.precioCompra, productos.id, productos.unidad, productos.codigo
	FROM productos_vendidos
	LEFT JOIN productos ON productos.id =  productos_vendidos.idProducto
	WHERE productos_vendidos.idReferencia = ? AND productos_vendidos.tipo = ?";
    $parametros = [$id, $tipo];
    return selectPrepare($sentencia, $parametros);
}

function obtenerProductosCotizados($id)
{
    $sentencia = "SELECT productos_cotizados.cantidad, productos_cotizados.precio, productos.nombre, productos.precioCompra, productos.id, productos.unidad, productos.codigo
	FROM productos_cotizados
	LEFT JOIN productos ON productos.id =  productos_cotizados.idProducto
	WHERE productos_cotizados.idCotizacion = ?";
    $parametros = [$id];
    return selectPrepare($sentencia, $parametros);
}

function obtenerVentasFiltradas($filtros)
{
    $sentencia = "SELECT 
        CASE 
            WHEN v.idMetodo IS NOT NULL THEN m.nombre
            WHEN v.simple IS NOT NULL THEN v.simple
        END AS metodo_pago,
        COUNT(*) AS ventas_totales,
        SUM(v.pagado) AS total_pagado
    FROM 
        ventas v
    LEFT JOIN 
        metodos m ON v.idMetodo = m.id";

    $parametros = [];

    if ($filtros->clienteId) {
        $sentencia .= " LEFT JOIN clientes c ON c.id = v.idCliente";
        $sentencia .= " WHERE c.id = ?";
        array_push($parametros, $filtros->clienteId);
    }

    if ($filtros->fechaInicio && $filtros->fechaFin) {
        $condicion = $filtros->clienteId ? " AND" : " WHERE";
        $sentencia .= "$condicion DATE(v.fecha) >= ? AND DATE(v.fecha) <= ?";
        array_push($parametros, $filtros->fechaInicio, $filtros->fechaFin);
    }

    $sentencia .= " GROUP BY 
        CASE 
            WHEN v.idMetodo IS NOT NULL THEN m.nombre
            WHEN v.simple IS NOT NULL THEN v.simple
        END
    ORDER BY 
        metodo_pago";

    return selectPrepare($sentencia, $parametros);
}

function obtenerCuentasApartadosFiltrados($filtros, $tipo)
{
    $sentencia = "SELECT 
        CASE 
            WHEN a.idMetodo IS NOT NULL THEN m.nombre
            WHEN a.simple IS NOT NULL THEN a.simple
        END AS metodo_pago,
        COUNT(DISTINCT ca.id) AS cuentas_apartados_totales,
        SUM(a.monto) AS total_pagado
    FROM 
        cuentas_apartados ca
    INNER JOIN
        abonos a ON a.idCuenta = ca.id
    LEFT JOIN
        metodos m ON a.idMetodo = m.id";

    if ($filtros->clienteId) {
        $sentencia .= " LEFT JOIN clientes c ON c.id = ca.idCliente";
    }

    $sentencia .= " WHERE ca.tipo = ?";
    $parametros = [$tipo];

    if ($filtros->clienteId) {
        $sentencia .= " AND c.id = ?";
        array_push($parametros, $filtros->clienteId);
    }

    if ($filtros->fechaInicio && $filtros->fechaFin) {
        $sentencia .= " AND DATE(ca.fecha) >= ? AND DATE(ca.fecha) <= ?";
        array_push($parametros, $filtros->fechaInicio, $filtros->fechaFin);
    }

    $sentencia .= " GROUP BY 
        CASE 
            WHEN a.idMetodo IS NOT NULL THEN m.nombre
            WHEN a.simple IS NOT NULL THEN a.simple
        END
    ORDER BY 
        metodo_pago";

    return selectPrepare($sentencia, $parametros);
}

function registrarDelivery($venta, $relacion, $id)
{
    $delivery = $venta->delivery;
    $choferes = is_array($delivery->idChofer) ? $delivery->idChofer : [$delivery->idChofer];

    foreach ($choferes as $idChofer) {
        // Si es '0', registrar nuevo chofer
        if ($idChofer === '0' || $idChofer === 0) {
            $chofer = $venta->chofer;
            $sentencia = "INSERT INTO choferes (nombre, telefono, tipo, ci) VALUES (?,?,?,?)";
            $parametros = [$chofer->nombre, $chofer->telefono, $chofer->tipo, $chofer->ci];
            insertar($sentencia, clean($parametros));
            $idChofer = obtenerUltimoId('choferes');
        }

        $sentencia = "INSERT INTO deliveries (costo, destino, gratis, idChofer, $relacion) VALUES (?,?,?,?,?)";
        $parametros = [$delivery->gratis ? 0 : $delivery->costo, $delivery->destino, intval($delivery->gratis), $idChofer, $id];
        insertar($sentencia, clean($parametros));
    }
}

function vender($venta)
{
    $venta->cliente = (isset($venta->cliente)) ? $venta->cliente : 0;
    $sentencia = "INSERT INTO ventas (fecha, total, pagado, origen, `simple`, idMetodo, idCliente, idUsuario) VALUES (?,?,?,?,?,?,?,?)";

    $parametros = [date("Y-m-d H:i:s"), $venta->total, $venta->pagado, $venta->origen, $venta->simple, $venta->idMetodo, $venta->cliente, $venta->usuario];

    $registrado = insertar($sentencia, clean($parametros));

    if (!$registrado)
        return false;

    $idVenta = obtenerUltimoId('ventas');
    $productosRegistrados = registrarProductosVendidos($venta->productos, $idVenta, 'venta');

    $valido = count($productosRegistrados) > 0;
    if (!$valido)
        return false;

    if (!$venta->delivery)
        return $idVenta;

    registrarDelivery($venta, 'idVenta', $idVenta);

    return $idVenta;
}


////AQUI JESUS

function editarApartadoCuenta($id, $cuenta, $tipo)
{
    $sentencia = "UPDATE cuentas_apartados SET total = ?, dias = ?, idCliente = ?, idUsuario = ? WHERE id = ?";
    $parametros = [
        $cuenta->total,
        $cuenta->dias,
        $cuenta->cliente,
        $cuenta->usuario,
        $id
    ];
    $resultado = editar($sentencia, clean($parametros));

    // Obtener productos vendidos anteriores
    $sentenciaObtener = "SELECT idProducto, cantidad, precio FROM productos_vendidos WHERE idReferencia = ? AND tipo = ?";
    $productosAnteriores = selectPrepare($sentenciaObtener, [$id, $tipo]);

    // Crear un mapa de productos actuales
    $productosActuales = [];
    foreach ($cuenta->productos as $producto) {
        $productosActuales[$producto->id] = $producto->cantidad;
    }

    // Actualizar, eliminar o insertar productos
    foreach ($productosAnteriores as $productoAnterior) {
        $idProducto = $productoAnterior->idProducto;
        $cantidadAnterior = $productoAnterior->cantidad;
        $precio = $productoAnterior->precio;

        if (isset($productosActuales[$idProducto])) {
            $cantidadNueva = $productosActuales[$idProducto];
            $productoEncontrado = array_filter($cuenta->productos, fn($p) => $p->id == $idProducto);
            $producto = reset($productoEncontrado);
            $precioNuevo = $producto->precio ?? $producto->precioVenta;
            if ($cantidadNueva != $cantidadAnterior || $precioNuevo != $precio) {
                $sentenciaActualizar = "UPDATE productos_vendidos SET cantidad = ?, precio = ? WHERE idReferencia = ? AND idProducto = ? AND tipo = ?";
                editar($sentenciaActualizar, [$cantidadNueva, $precioNuevo, $id, $idProducto, $tipo]);
            }
            unset($productosActuales[$idProducto]);
        } else {
            $sentenciaEliminar = "DELETE FROM productos_vendidos WHERE idReferencia = ? AND idProducto = ? AND tipo = ?";
            eliminar($sentenciaEliminar, [$id, $idProducto, $tipo]);
        }
    }

    // Insertar nuevos productos
    foreach ($productosActuales as $idProducto => $cantidadNueva) {
        $producto = array_filter($cuenta->productos, fn($p) => $p->id == $idProducto);
        $producto = reset($producto);
        $precio = $producto->precio ?? $producto->precioVenta;

        $sentenciaInsertar = "INSERT INTO productos_vendidos (fecha, cantidad, precio, idProducto, idReferencia, tipo) VALUES (?,?,?,?,?,?)";
        $parametrosInsertar = [
            date('Y-m-d H:i:s'),
            $cantidadNueva,
            $precio,
            $idProducto,
            $id,
            $tipo
        ];
        insertar($sentenciaInsertar, $parametrosInsertar);
    }

    // Eliminar todos los deliveries anteriores asociados a esta cuenta
    $sentenciaEliminarDelivery = "DELETE FROM deliveries WHERE idCuenta = ?";
    eliminar($sentenciaEliminarDelivery, [$id]);

    // Registrar los nuevos deliveries (pueden ser varios choferes)
    if (isset($cuenta->delivery)) {
        registrarDelivery($cuenta, 'idCuenta', $id);
    }

    return $resultado;
}

function editarVenta($id, $venta)
{
    $sentencia = "UPDATE ventas SET total = ?, pagado = ?, origen = ?, `simple` = ?, idMetodo = ?, idCliente = ?, idUsuario = ? WHERE id = ?";
    $parametros = [
        $venta->total,
        $venta->pagado,
        $venta->origen,
        $venta->simple,
        $venta->idMetodo,
        $venta->cliente,
        $venta->usuario,
        $id
    ];
    $resultado = editar($sentencia, clean($parametros));

    // Obtener productos vendidos anteriores
    $sentenciaObtener = "SELECT idProducto, cantidad, precio FROM productos_vendidos WHERE idReferencia = ? AND tipo = ?";
    $productosAnteriores = selectPrepare($sentenciaObtener, [$id, 'venta']);

    // Crear un mapa de productos actuales
    $productosActuales = [];
    foreach ($venta->productos as $producto) {
        $productosActuales[$producto->id] = [
            'cantidad' => $producto->cantidad,
            'precio' => $producto->precio ?? $producto->precioVenta ?? null
        ];
    }

    // Actualizar, eliminar o insertar productos
    foreach ($productosAnteriores as $productoAnterior) {
        $idProducto = $productoAnterior->idProducto;
        $cantidadAnterior = $productoAnterior->cantidad;
        $precioAnterior = $productoAnterior->precio;

        if (isset($productosActuales[$idProducto])) {
            $cantidadNueva = $productosActuales[$idProducto]['cantidad'];
            $precioNuevo = $productosActuales[$idProducto]['precio'];

            if ($cantidadNueva != $cantidadAnterior || $precioNuevo != $precioAnterior) {
                $sentenciaActualizar = "UPDATE productos_vendidos SET cantidad = ?, precio = ? WHERE idReferencia = ? AND idProducto = ? AND tipo = ?";
                editar($sentenciaActualizar, [$cantidadNueva, $precioNuevo, $id, $idProducto, 'venta']);
            }
            unset($productosActuales[$idProducto]);
        } else {
            $sentenciaEliminar = "DELETE FROM productos_vendidos WHERE idReferencia = ? AND idProducto = ? AND tipo = ?";
            eliminar($sentenciaEliminar, [$id, $idProducto, 'venta']);
        }
    }

    // Insertar nuevos productos
    foreach ($productosActuales as $idProducto => $datosProducto) {
        $producto = current(array_filter($venta->productos, fn($p) => $p->id == $idProducto));
        $precio = $producto->precio ?? $producto->precioVenta;

        $sentenciaInsertar = "INSERT INTO productos_vendidos (fecha, cantidad, precio, idProducto, idReferencia, tipo) VALUES (?,?,?,?,?,?)";
        $parametrosInsertar = [
            date('Y-m-d H:i:s'),
            $datosProducto['cantidad'],
            $precio,
            $idProducto,
            $id,
            'venta'
        ];
        insertar($sentenciaInsertar, $parametrosInsertar);
    }

    // Eliminar todos los deliveries anteriores asociados a esta venta
    $sentenciaEliminarDelivery = "DELETE FROM deliveries WHERE idVenta = ?";
    eliminar($sentenciaEliminarDelivery, [$id]);

    // Registrar los nuevos deliveries (pueden ser varios choferes)
    if (isset($venta->delivery)) {
        registrarDelivery($venta, 'idVenta', $id);
    }

    return $resultado;
}

function eliminarVenta($id)
{
    // Eliminar productos vendidos relacionados
    $sentenciaEliminarProductos = "DELETE FROM productos_vendidos WHERE idReferencia = ? AND tipo = 'venta'";
    eliminar($sentenciaEliminarProductos, $id);

    // Eliminar delivery relacionado
    $sentenciaEliminarDelivery = "DELETE FROM deliveries WHERE idVenta = ?";
    eliminar($sentenciaEliminarDelivery, $id);

    // Eliminar la venta
    $sentenciaEliminarVenta = "DELETE FROM ventas WHERE id = ?";
    return eliminar($sentenciaEliminarVenta, $id);
}


function agregarCuentaApartado($venta)
{
    $sentencia = "INSERT INTO cuentas_apartados (fecha, total, dias, tipo, idCliente, idUsuario) VALUES (?,?,?,?,?,?)";

    $ahora = date("Y-m-d H:i:s");
    $parametros = [$ahora, $venta->total, $venta->dias, $venta->tipo, $venta->cliente, $venta->usuario];

    $registrado = insertar($sentencia, clean($parametros));

    if (!$registrado)
        return false;

    $idCuenta = obtenerUltimoId('cuentas_apartados');

    if ($venta->pagado) {
        $sentencia = "INSERT INTO abonos (fecha, monto, origen, `simple`, idMetodo, idCuenta) VALUES (?,?,?,?,?,?)";

        $parametros = [$ahora, $venta->pagado, $venta->origen, $venta->simple, $venta->idMetodo, $idCuenta];

        insertar($sentencia, clean($parametros));
    }

    $productosRegistrados = registrarProductosVendidos($venta->productos, $idCuenta, $venta->tipo);

    $valido = count($productosRegistrados) > 0;
    if (!$valido)
        return false;

    if (!$venta->delivery)
        return $idCuenta;
    registrarDelivery($venta, 'idCuenta', $idCuenta);

    return $idCuenta;
}

////AQUI JESUS

function editarCuentaApartado($id, $cuenta)
{
    $sentencia = "UPDATE cuentas_apartados SET total = ?, dias = ?, tipo = ?, idCliente = ?, idUsuario = ? WHERE id = ?";
    $parametros = [
        $cuenta->total,
        $cuenta->dias,
        $cuenta->tipo,
        $cuenta->cliente,
        $cuenta->usuario,
        $id
    ];
    $resultado = editar($sentencia, clean($parametros));

    // Eliminar productos vendidos anteriores
    $sentenciaEliminar = "DELETE FROM productos_vendidos WHERE idReferencia = ? AND tipo = ?";
    eliminar($sentenciaEliminar, $id);

    // Registrar nuevos productos vendidos
    registrarProductosVendidos($cuenta->productos, $id, $cuenta->tipo);

    // Actualizar delivery si existe
    if (isset($cuenta->delivery)) {
        // Eliminar delivery anterior
        $sentenciaEliminarDelivery = "DELETE FROM deliveries WHERE idCuenta = ?";
        eliminar($sentenciaEliminarDelivery, $id);

        // Registrar nuevo delivery
        registrarDelivery($cuenta, 'idCuenta', $id);
    }

    return $resultado;
}

function eliminarCuentaApartado($id)
{
    // Eliminar productos vendidos relacionados
    $sentenciaEliminarProductos = "DELETE FROM productos_vendidos WHERE idReferencia = ? AND tipo = 'cuenta'";
    eliminar($sentenciaEliminarProductos, $id);

    // Eliminar abonos relacionados
    $sentenciaEliminarAbonos = "DELETE FROM abonos WHERE idCuenta = ?";
    eliminar($sentenciaEliminarAbonos, $id);

    // Eliminar delivery relacionado
    $sentenciaEliminarDelivery = "DELETE FROM deliveries WHERE idCuenta = ?";
    eliminar($sentenciaEliminarDelivery, $id);

    // Eliminar la cuenta_apartado
    $sentenciaEliminarCuenta = "DELETE FROM cuentas_apartados WHERE id = ?";
    return eliminar($sentenciaEliminarCuenta, $id);
}




function agregarCotizacion($venta)
{
    $sentencia = "INSERT INTO cotizaciones(fecha, total, hasta, idCliente, idUsuario) VALUES (?,?,?,?,?)";
    $parametros = [date("Y-m-d H:i:s"), $venta->total, $venta->hasta, $venta->cliente, $venta->usuario];

    insertar($sentencia, clean($parametros));
    $idCotizacion = obtenerUltimoId('cotizaciones');

    $productosRegistrados = registrarProductosCotizados($venta->productos, $idCotizacion);

    if (count($productosRegistrados) > 0)
        return $idCotizacion;
}

function registrarProductosVendidos($productos, $idReferencia, $tipo)
{
    $sentencia = "INSERT INTO productos_vendidos (fecha, cantidad, precio, idProducto, idReferencia, tipo) VALUES (?,?,?,?,?,?)";
    $resultados = [];

    foreach ($productos as $producto) {
        $parametros = [date('Y-m-d H:i:s'), $producto->cantidad, $producto->precio, $producto->id, $idReferencia, $tipo];
        $productoRegistrado = insertar($sentencia, $parametros);
        if ($productoRegistrado)
            array_push($resultados, 1);
    }

    return $resultados;
}

function registrarProductosCotizados($productos, $idCotizacion)
{
    $sentencia = "INSERT INTO productos_cotizados (cantidad, precio, idProducto, idCotizacion) VALUES(?,?,?,?)";
    $resultados = [];

    foreach ($productos as $producto) {
        $parametros = [$producto->cantidad, $producto->precio, $producto->id, $idCotizacion];
        $productoRegistrado = insertar($sentencia, $parametros);
        if ($productoRegistrado)
            array_push($resultados, 1);
    }

    return $resultados;
}

function obtenerCuentaUsandoAbono($id)
{
    $sentencia = "SELECT cuentas_apartados.id
        FROM abonos
        LEFT JOIN cuentas_apartados ON cuentas_apartados.id = abonos.idCuenta
        WHERE abonos.id = ?";
    return selectRegresandoObjeto($sentencia, [$id]);
}

function montoPorPagarCuentaApartado($id)
{
    $sentencia = "SELECT (cuentas_apartados.total - IFNULL(SUM(abonos.monto), 0)) AS porPagar
        FROM cuentas_apartados
        LEFT JOIN abonos ON cuentas_apartados.id = abonos.idCuenta
        WHERE cuentas_apartados.id = ?";

    return selectRegresandoObjeto($sentencia, [$id])->porPagar;
}

function editarAbono($id)
{
    $sentencia = "SELECT * FROM abonos WHERE id = ?";
    return selectRegresandoObjeto($sentencia, [$id]);
}

function obtenerCuentaApartado($id)
{
    return selectRegresandoObjeto("SELECT cuentas_apartados.*,
        clientes.nombre AS nombreCliente,
        IFNULL(SUM(abonos.monto), 0) AS pagado,
        (total - IFNULL(SUM(abonos.monto), 0)) AS porPagar
        FROM cuentas_apartados
        LEFT JOIN clientes ON clientes.id = cuentas_apartados.idCliente
        LEFT JOIN abonos ON cuentas_apartados.id = abonos.idCuenta
        WHERE cuentas_apartados.id = ?", [$id]);
}

/* Abonos */

function obtenerTodosLosAbonosFiltrados($filtros)
{
    $sql = "SELECT 
        abonos.*, 
        metodos.nombre AS metodo, 
        cuentas_apartados.tipo, 
        cuentas_apartados.id AS idCuenta, 
        clientes.nombre AS nombreCliente
    FROM abonos
    LEFT JOIN metodos ON abonos.idMetodo = metodos.id
    LEFT JOIN cuentas_apartados ON abonos.idCuenta = cuentas_apartados.id
    LEFT JOIN clientes ON cuentas_apartados.idCliente = clientes.id
    WHERE 1=1";

    $parametros = [];

    if (!empty($filtros->clienteId)) {
        $sql .= " AND clientes.id = ?";
        $parametros[] = $filtros->clienteId;
    }
    if (!empty($filtros->fechaInicio) && !empty($filtros->fechaFin)) {
        $sql .= " AND (DATE(abonos.fecha) >= ? AND DATE(abonos.fecha) <= ?)";
        $parametros[] = $filtros->fechaInicio;
        $parametros[] = $filtros->fechaFin;
    }
    if (!empty($filtros->tipo)) {
        $sql .= " AND cuentas_apartados.tipo = ?";
        $parametros[] = $filtros->tipo;
    }

    $sql .= " ORDER BY abonos.fecha DESC";

    return selectPrepare($sql, $parametros);
}

function obtenerAbonosPorCuentaApartado($id)
{
    $abonos = selectPrepare("SELECT abonos.*, metodos.nombre AS metodo FROM abonos
        LEFT JOIN metodos ON abonos.idMetodo = metodos.id
        WHERE abonos.idCuenta = ?", [$id]);

    $cuentaApartado = obtenerCuentaApartado($id);

    if ($abonos === false || $cuentaApartado === false)
        return false;

    return ['abonos' => $abonos, 'cuentaApartado' => $cuentaApartado];
}

function registrarAbono($abono)
{
    $sentencia = "INSERT INTO abonos (fecha, monto, origen, `simple`, idMetodo, idCuenta) VALUES (?,?,?,?,?,?)";

    $parametros = [date('Y-m-d H:i:s'), $abono->monto, $abono->origen, $abono->simple, $abono->idMetodo, $abono->idCuenta];

    insertar($sentencia, clean($parametros));
    return obtenerUltimoId('abonos');
}

function actualizarAbono($abono)
{
    $sentencia = "UPDATE abonos SET monto = ?, origen = ?, `simple` = ?, idMetodo = ?, idCuenta = ? WHERE id = ?";
    $parametros = [
        $abono->monto,
        $abono->origen,
        $abono->simple,
        is_numeric($abono->idMetodo) ? $abono->idMetodo : null,
        $abono->idCuenta,
        $abono->id
    ];

    return editar($sentencia, clean($parametros));
}

/*                                                                                                  
 __   __  _______  __   __  _______  ______    ___   _______  _______ 
|  | |  ||       ||  | |  ||   _   ||    _ |  |   | |       ||       |
|  |_|  ||  _____||   |_| ||  |_|  ||   | ||  |   | |   _   ||  _____|
|       || |_____ |       ||       ||   |_||_ |   | |  | |  || |_____ 
|       ||_____  ||       ||       ||    __  ||   | |  |_|  ||_____  |
|       | _____| ||       ||   _   ||   |  | ||   | |       | _____| |
|_______||_______||_______||__| |__||___|  |_||___| |_______||_______|
                                                                    
*/

// TODO -> los calculos de estadísticas de ventas (SUM(total) FROM ventas) deberían hacerse directo a la tabla "productos_vendidos" filtrando por tipo = venta, ya que el total de ventas puede incluir el delivery pagado por el cliente, que no sería una ganancia técnicamente
function obtenerTotalVentasPorMesUsuario($idUsuario, $anio)
{
    $sentencia = "SELECT MONTH(fecha) AS mes, IFNULL(SUM(total), 0) AS totalVentas FROM ventas 
        WHERE YEAR(fecha) = ? AND idUsuario = ?
        GROUP BY MONTH(fecha) ORDER BY mes ASC";
    $parametros = [$anio, $idUsuario];
    return selectPrepare($sentencia, $parametros);
}

function calcularTotalIngresosUsuario($idUsuario)
{
    $sentencia = "SELECT
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE idUsuario = ?) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos) AS totalIngresos";
    $parametros = [$idUsuario];
    return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function calcularTotalIngresosHoyUsuario($idUsuario)
{
    $sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE DATE(fecha) = CURDATE() AND idUsuario = ?) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE DATE(fecha) = CURDATE()) AS totalIngresos";
    $parametros = [$idUsuario];
    return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function calcularTotalIngresosSemanaUsuario($idUsuario)
{
    $sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE WEEK(fecha) = WEEK(NOW()) AND idUsuario = ?) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE WEEK(fecha) = WEEK(NOW())) AS totalIngresos";
    $parametros = [$idUsuario];
    return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function calcularTotalIngresosMesUsuario($idUsuario)
{
    $sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE()) AND idUsuario = ?) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())) AS totalIngresos";
    $parametros = [$idUsuario];
    return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function obtenerVentasPorUsuario()
{
    $sentencia = "SELECT usuarios.usuario, IFNULL(SUM(ventas.total), 0) AS totalVentas
    FROM ventas
	INNER JOIN usuarios ON usuarios.id = ventas.idUsuario
	GROUP BY usuarios.id";
    return selectQuery($sentencia);
}

function iniciarSesion($usuario)
{
    $sentencia = "SELECT * FROM usuarios WHERE usuario = ?";
    $parametros = [$usuario->usuario];
    $resultado = selectRegresandoObjeto($sentencia, $parametros);

    if (!$resultado)
        return false;

    $loginCorrecto = verificarPassword($resultado->id, $usuario->password);

    if (!$loginCorrecto)
        return false;

    // Destruimos la sesión anterior
    cerrarSesion();

    // Iniciamos una nueva sesión
    session_start();
    $_SESSION['userid'] = $resultado->id;

    $datos = [
        "id" => $resultado->id,
        "usuario" => $resultado->usuario,
        "nombre" => $resultado->nombre
    ];

    return ["estado" => $loginCorrecto, "usuario" => $datos];
}

function cerrarSesion()
{
    session_unset();
    session_destroy();
    return true;
}

function obtenerEstadoAutenticacion()
{
    $id = $_SESSION['userid'];

    if (!$id) {
        return ['usuario' => null, 'permisos' => null];
    }

    $usuario = obtenerUsuarioPorId($id);

    return [
        'usuario' => $usuario,
        'permisos' => $usuario->permisos,
    ];
}

function verificarPassword($idUsuario, $password)
{
    $sentencia = "SELECT password FROM usuarios WHERE id = ?";
    $parametros = [$idUsuario];
    $resultado = selectRegresandoObjeto($sentencia, $parametros);
    return password_verify($password, $resultado->password);
}

function cambiarPassword($idUsuario, $datos)
{
    $actualCoincide = verificarPassword($idUsuario, $datos->oldPassword);

    if (!$actualCoincide) {
        return ['registrado' => false, 'mensaje' => 'La contraseña actual es incorrecta.'];
    }

    if ($datos->password !== $datos->confirmation) {
        return ['registrado' => false, 'mensaje' => 'La contraseña y su confirmación no coinciden.'];
    }

    $sentencia = "UPDATE usuarios SET password = ? WHERE id = ?";
    $hasheado = password_hash($datos->password, PASSWORD_BCRYPT);
    $parametros = [$hasheado, $idUsuario];
    $resultado = editar($sentencia, $parametros);
    return ['registrado' => $resultado, 'mensaje' => 'La contraseña se actualizó exitosamente.'];
}

function registrarUsuario($usuario)
{
    $existente = (int) selectRegresandoObjeto("SELECT COUNT(*) AS existente
        FROM usuarios
        WHERE usuario = ?", [$usuario->usuario])->existente;

    if ($existente > 0) {
        return [
            'registrado' => false,
            'mensaje' => 'El nombre de usuario ya existe.'
        ];
    }

    if ($usuario->password !== $usuario->confirmacion) {
        return [
            'registrado' => false,
            'mensaje' => 'La contraseña y su confirmacion no coinciden.'
        ];
    }

    $sentencia = "INSERT INTO usuarios (usuario, nombre, telefono, password, idRol) VALUES (?,?,?,?,?)";
    $hasheado = password_hash($usuario->password, PASSWORD_BCRYPT);
    $parametros = [$usuario->usuario, $usuario->nombre, $usuario->telefono, $hasheado, $usuario->idRol];
    $resultado = insertar($sentencia, $parametros);

    return [
        'registrado' => $resultado,
        'mensaje' => 'El usuario fue registrado con éxito.',
    ];
}

function obtenerUsuarioPorId($id)
{
    $sentencia = "SELECT u.id, u.usuario, u.nombre, u.telefono, u.idRol,
        r.nombre AS rol, r.permisos
        FROM usuarios AS u
        LEFT JOIN roles AS r ON r.id = u.idRol
        WHERE u.id = ?";
    return selectRegresandoObjeto($sentencia, [$id]);
}

function editarUsuario($usuario)
{
    $sentencia = "UPDATE usuarios SET usuario = ?, nombre = ?, telefono = ?, idRol = ? WHERE id = ?";

    if ($usuario->id == 1) {
        $usuario->idRol = 1;
    }

    $parametros = [$usuario->usuario, $usuario->nombre, $usuario->telefono, $usuario->idRol, $usuario->id];
    return editar($sentencia, $parametros);
}

function eliminarUsuario($id)
{
    $sentencia = "DELETE FROM usuarios WHERE id = ?";
    return eliminar($sentencia, $id);
}

function eliminarAbono($id)
{
    $sentencia = "DELETE FROM abonos WHERE id = ?";
    return eliminar($sentencia, $id);
}

function obtenerUsuarios()
{
    $sentencia = "SELECT id, usuario, nombre, telefono, idRol FROM usuarios";
    return selectQuery($sentencia);
}

/*

 _______  ___      ___   _______  __    _  _______  _______  _______  _______ 
|       ||   |    |   | |       ||  |  | ||       ||       ||       ||       |
|       ||   |    |   | |    ___||   |_| ||_     _||    ___||  _____|
|       ||   |    |   | |   |___ |       |  |   |  |   |___ | |_____ 
|      _||   |___ |   | |    ___||  _    |  |   |  |    ___||_____  |
|     |_ |       ||   | |   |___ | | |   |  |   |  |   |___  _____| |
|_______||_______||___| |_______||_|  |__|  |___|  |_______||_______|

*/

function obtenerVentasPorCliente()
{
    $sentencia = "SELECT clientes.nombre, IFNULL(SUM(ventas.total), 0) AS totalVentas  FROM ventas
	INNER JOIN clientes ON clientes.id = ventas.idCliente
	GROUP BY clientes.id";
    return selectQuery($sentencia);
}

function obtenerClientesPorNombre($nombre)
{
    $sentencia = "SELECT * FROM clientes WHERE nombre LIKE ?";
    $parametros = ["%" . $nombre . "%"];
    return selectPrepare($sentencia, $parametros);
}

function obtenerClientes()
{
    $sentencia = "SELECT * FROM clientes";
    return selectQuery($sentencia);
}

function registrarCliente($cliente)
{
    $sentencia = "INSERT INTO clientes (nombre, telefono, tipo, ci, direccion) VALUES (?,?,?,?,?)";
    $parametros = [$cliente->nombre, $cliente->telefono, $cliente->tipo, $cliente->ci, $cliente->direccion];
    return insertar($sentencia, clean($parametros));
}

function obtenerClientePorId($id)
{
    $sentencia = "SELECT * FROM clientes WHERE id = ?";
    return selectRegresandoObjeto($sentencia, [$id]);
}

function editarCliente($cliente)
{
    $sentencia = "UPDATE clientes SET nombre = ?, telefono = ?, tipo = ?, ci = ?, direccion = ? WHERE id = ?";
    $parametros = [$cliente->nombre, $cliente->telefono, $cliente->tipo, $cliente->ci, $cliente->direccion, $cliente->id];
    return editar($sentencia, $parametros);
}

function eliminarCliente($id)
{
    $sentencia = "DELETE FROM clientes WHERE id = ?";
    return eliminar($sentencia, $id);
}


/* Choferes */
function obtenerChoferes()
{
    $pagado = "SELECT IFNULL(SUM(monto),0) FROM pagos_choferes WHERE pagos_choferes.idChofer = choferes.id)";
    $sentencia = "SELECT choferes.*,
        (IFNULL(SUM(deliveries.costo),0) - ($pagado) as deuda
        FROM choferes
        LEFT JOIN deliveries ON deliveries.idChofer = choferes.id
        GROUP BY choferes.id;";
    return selectQuery($sentencia);
}

function obtenerChoferesPorNombre($nombre)
{
    $pagado = "SELECT IFNULL(SUM(monto),0) FROM pagos_choferes WHERE pagos_choferes.idChofer = choferes.id";
    $sentencia = "SELECT choferes.*,
        (IFNULL(SUM(deliveries.costo),0) - ($pagado)) as deuda
        FROM choferes
        LEFT JOIN deliveries ON deliveries.idChofer = choferes.id
        LEFT JOIN pagos_choferes ON pagos_choferes.idChofer = choferes.id
        WHERE choferes.nombre LIKE ?
        GROUP BY choferes.id;";
    $parametros = ["%{$nombre}%"];
    return selectPrepare($sentencia, $parametros);
}

function obtenerChoferPorId($id)
{
    $pagado = "SELECT IFNULL(SUM(monto),0) FROM pagos_choferes WHERE pagos_choferes.idChofer = choferes.id)";
    $sentencia = "SELECT choferes.*,
        (IFNULL(SUM(deliveries.costo),0) - ($pagado)) as deuda
        FROM choferes
        LEFT JOIN deliveries ON deliveries.idChofer = choferes.id
        LEFT JOIN pagos_choferes ON pagos_choferes.idChofer = choferes.id
        WHERE choferes.id = ?
        GROUP BY choferes.id;";
    return selectRegresandoObjeto($sentencia, [$id]);
}

function editarChofer($chofer)
{
    $sentencia = "UPDATE choferes SET nombre = ?, telefono = ?, tipo = ?, ci = ? WHERE id = ?";
    $parametros = [$chofer->nombre, $chofer->telefono, $chofer->tipo, $chofer->ci, $chofer->id];
    return editar($sentencia, $parametros);
}

function registrarPagoChofer($pago)
{
    $sentencia = "INSERT INTO pagos_choferes (monto, idChofer) VALUES (?,?)";
    $parametros = [$pago->monto, $pago->idChofer];
    return insertar($sentencia, $parametros);
}

function obtenerDeliveries($filtros)
{
    $sentencia = "SELECT deliveries.*, choferes.nombre as nombreChofer, 
        COALESCE(ventas.fecha, cuentas.fecha) as fecha,
        COALESCE(clientes.nombre, clientes2.nombre, 'MOSTRADOR') as nombreCliente
        FROM deliveries
        LEFT JOIN choferes ON deliveries.idChofer = choferes.id
        LEFT JOIN ventas ON deliveries.idVenta = ventas.id
        LEFT JOIN cuentas_apartados AS cuentas ON deliveries.idCuenta = cuentas.id
        LEFT JOIN clientes ON ventas.idCliente = clientes.id
        LEFT JOIN clientes AS clientes2 ON cuentas.idCliente = clientes2.id";

    $parametros = [];
    if ($filtros->choferId) {
        $sentencia .= " WHERE deliveries.idChofer = ?";
        array_push($parametros, $filtros->choferId);
    }

    if ($filtros->fechaInicio && $filtros->fechaFin) {
        $sentencia .= ($filtros->choferId ? ' AND ' : ' WHERE ')
            . " (DATE(COALESCE(ventas.fecha, cuentas.fecha)) >= ? 
            AND DATE(COALESCE(ventas.fecha, cuentas.fecha)) <= ?)";
        array_push($parametros, $filtros->fechaInicio, $filtros->fechaFin);
    }

    $sentencia .= " ORDER BY COALESCE(ventas.fecha, cuentas.fecha) DESC";

    return selectPrepare($sentencia, $parametros);
}

/* PROVEEDORES */

function obtenerProveedores()
{
    $sentencia = "SELECT proveedores.*,
        IFNULL(SUM(entradas.monto), 0) AS total
        FROM proveedores
        LEFT JOIN productos ON productos.proveedor = proveedores.id
        LEFT JOIN entradas ON entradas.idProducto = productos.id
        GROUP BY proveedores.id;";

    $sentencia2 = "SELECT proveedores.id,
        IFNULL(SUM(pagos_proveedores.monto), 0) AS pagado
        FROM proveedores
        LEFT JOIN pagos_proveedores ON pagos_proveedores.idProveedor = proveedores.id
        GROUP BY proveedores.id;";

    $proveedores = selectQuery($sentencia);
    $pagados = selectQuery($sentencia2);

    foreach ($proveedores as $index => $proveedor) {
        $fila = $pagados[$index];
        if ($proveedor->id === $fila->id) {
            $proveedor->pagado = $fila->pagado;

            $proveedor->deuda = ($proveedor->total * 100) - ($fila->pagado * 100);
            $proveedor->deuda = $proveedor->deuda / 100;
        }
    }

    return $proveedores;
}

function obtenerProveedorPorId($id)
{
    $sentencia = "SELECT proveedores.*,
        IFNULL(SUM(entradas.monto), 0) AS total
        FROM proveedores
        LEFT JOIN productos ON productos.proveedor = proveedores.id
        LEFT JOIN entradas ON entradas.idProducto = productos.id
        WHERE proveedores.id = ?";

    $sentencia2 = "SELECT IFNULL(SUM(pagos_proveedores.monto), 0) AS pagado
        FROM proveedores
        LEFT JOIN pagos_proveedores ON pagos_proveedores.idProveedor = proveedores.id
        WHERE proveedores.id = ?;";

    $proveedor = selectRegresandoObjeto($sentencia, [$id]);
    $pagado = selectRegresandoObjeto($sentencia2, [$id])->pagado;
    $proveedor->pagado = $pagado;
    $proveedor->deuda = $proveedor->total - $pagado;

    return $proveedor;
}

function registrarProveedor($datos)
{
    $sentencia = "INSERT INTO proveedores (nombre, telefono, rif, direccion) VALUES (?,?,?,?);";
    $parametros = [$datos->nombre, $datos->telefono, $datos->rif, $datos->direccion];
    return insertar($sentencia, $parametros);
}

function editarProveedor($datos)
{
    $sentencia = "UPDATE proveedores SET nombre = ?, telefono = ?, rif = ?, direccion = ? WHERE id = ?";
    $parametros = [$datos->nombre, $datos->telefono, $datos->rif, $datos->direccion, $datos->id];
    return editar($sentencia, $parametros);
}

function obtenerPagosProveedor($id)
{
    $sentencia = "SELECT pp.*, u.nombre AS nombreUsuario
        FROM pagos_proveedores AS pp
        LEFT JOIN usuarios as u ON u.id = pp.idUsuario
        WHERE pp.idProveedor = ?
        ORDER BY pp.fecha DESC";

    $pagos = selectPrepare($sentencia, [$id]);

    return [
        'proveedor' => obtenerProveedorPorId($id),
        'pagos' => $pagos,
    ];
}

function pagarProveedor($pago)
{
    $sentencia = "INSERT INTO pagos_proveedores (fecha, monto, idProveedor, idUsuario) VALUES (?,?,?,?)";
    $parametros = [date('Y-m-d H:i:s'), $pago->monto, $pago->idProveedor, $pago->idUsuario];
    return insertar($sentencia, $parametros);
}

/* ROLEs */

function obtenerRoles()
{
    $sentencia = "SELECT roles.*, COUNT(usuarios.id) AS numUsuarios
        FROM roles
        LEFT JOIN usuarios ON usuarios.idRol = roles.id
        GROUP BY roles.id;";

    return selectQuery($sentencia);
}

function obtenerRolPorId($id)
{
    $sentencia = "SELECT roles.*, COUNT(usuarios.id) AS numUsuarios
        FROM roles
        LEFT JOIN usuarios ON usuarios.idRol = roles.id
        WHERE roles.id = ?
        GROUP BY roles.id;";

    return selectRegresandoObjeto($sentencia, [$id]);
}

function registrarRol($datos)
{
    $sentencia = "INSERT INTO roles (nombre, permisos) VALUES (?,?);";
    $parametros = [$datos->nombre, $datos->permisos];
    return insertar($sentencia, $parametros);
}

function editarRol($datos)
{
    $sentencia = "UPDATE roles SET nombre = ?, permisos = ? WHERE id = ?";
    $parametros = [$datos->nombre, $datos->permisos, $datos->id];
    return editar($sentencia, $parametros);
}

/*

 _______  ______    _______  ______   __   __  _______  _______  _______  _______ 
|       ||    _ |  |       ||      | |  | |  ||       ||       ||       ||       |
|    _  ||   | ||  |   _   ||  _    ||  | |  ||       ||_     _||    ___||    ___|
|   |_| ||   |_||_ |  | |  || | |   ||  |_|  ||       |  |   |  |   |___ |   | __ 
|    ___||    __  ||  |_|  || |_|   ||       ||      _|  |   |  |    ___||   ||  |
|   |    |   |  | ||       ||       ||       ||     |_   |   |  |   |___ |   |_| |
|___|    |___|  |_||_______||______| |_______||_______|  |___|  |_______||_______|

*/
function obtenerProductosMasVendidos($limite)
{
    $sentencia = "SELECT IFNULL(SUM(productos_vendidos.cantidad * productos_vendidos.precio), 0) AS total, IFNULL(SUM(productos_vendidos.cantidad), 0) AS unidades,
	productos.nombre
    FROM productos_vendidos INNER JOIN productos ON productos.id = productos_vendidos.idProducto
	WHERE productos_vendidos.tipo = 'venta'
	GROUP BY productos_vendidos.idProducto
	ORDER BY total DESC
	LIMIT ?";
    return selectPrepare($sentencia, [$limite]);
}

function obtenerExistencia($id = null)
{
    $salidas = "SELECT COALESCE(SUM(productos_vendidos.cantidad), 0) FROM productos_vendidos WHERE productos_vendidos.idProducto = productos.id";

    $removidos = "SELECT COALESCE(SUM(productos_removidos.cantidad), 0) FROM productos_removidos WHERE productos_removidos.idProducto = productos.id";

    $entradas = "SELECT COALESCE(SUM(entradas.cantidad), 0) FROM entradas WHERE entradas.idProducto = productos.id";

    $sentencia = "SELECT productos.id AS id, productos.nombre AS nombre, productos.precioVenta AS precioVenta, productos.precioCompra AS precioCompra, (($entradas) - ($removidos) - ($salidas)) AS existencia
    FROM productos";

    if (!$id) {
        return selectQuery($sentencia);
    }

    return selectRegresandoObjeto("$sentencia WHERE productos.id = ?;", [$id]);
}

function agregarExistenciaProducto($entrada)
{
    $sentencia = "INSERT INTO entradas (fecha, monto, cantidad, idProducto, idUsuario) VALUES (?,?,?,?,?)";
    $parametros = [date('Y-m-d H:i:s'), $entrada->monto, $entrada->cantidad, $entrada->id, $entrada->usuario];
    return insertar($sentencia, $parametros);
}

function removerExistenciaProducto($producto)
{
    $sentencia = "INSERT INTO productos_removidos (fecha, cantidad, idProducto, idUsuario) VALUES (?,?,?,?)";
    $parametros = [date('Y-m-d H:i:s'), $producto->cantidad, $producto->id, $producto->usuario];
    return insertar($sentencia, $parametros);
}

function obtenerHistorialInventario($proveedor = null, $productoId = null, $fechaInicio = null, $fechaFin = null)
{
    $antiguo = "SELECT MIN(e1.fecha) FROM entradas AS e1 WHERE e1.idProducto = e.idProducto";

    // --- ENTRADAS ---
    $whereEntradas = " WHERE 1=1";
    $paramsEntradas = [];
    if ($proveedor) {
        $whereEntradas .= " AND pr.id = ?";
        $paramsEntradas[] = $proveedor;
    }
    if ($productoId) {
        $whereEntradas .= " AND e.idProducto = ?";
        $paramsEntradas[] = $productoId;
    }
    if ($fechaInicio && $fechaFin) {
        $whereEntradas .= " AND DATE(e.fecha) >= ? AND DATE(e.fecha) <= ?";
        $paramsEntradas[] = $fechaInicio;
        $paramsEntradas[] = $fechaFin;
    }
    $sentencia1 = "SELECT e.fecha, e.cantidad, e.monto,
        p.nombre AS nombreProducto, u.usuario AS nombreUsuario,
        '+' AS signo, pr.nombre AS nombreProveedor,
        IF(($antiguo) = e.fecha, 'Registro', 'Reposición') AS tipo
        FROM entradas AS e
        LEFT JOIN productos AS p ON p.id = e.idProducto
        LEFT JOIN usuarios AS u ON e.idUsuario = u.id
        INNER JOIN proveedores AS pr ON p.proveedor = pr.id
        $whereEntradas";

    // --- VENTAS ---
    $whereVentas = " WHERE 1=1";
    $paramsVentas = [];
    if ($proveedor) {
        $whereVentas .= " AND pr.id = ?";
        $paramsVentas[] = $proveedor;
    }
    if ($productoId) {
        $whereVentas .= " AND v.idProducto = ?";
        $paramsVentas[] = $productoId;
    }
    if ($fechaInicio && $fechaFin) {
        $whereVentas .= " AND DATE(v.fecha) >= ? AND DATE(v.fecha) <= ?";
        $paramsVentas[] = $fechaInicio;
        $paramsVentas[] = $fechaFin;
    }
    $sentencia2 = "SELECT v.fecha, v.cantidad, v.precio as monto,
        'Venta' AS tipo, p.nombre AS nombreProducto,
        u.usuario AS nombreUsuario, '-' AS signo,
        c.nombre AS nombreCliente, pr.nombre AS nombreProveedor
        FROM productos_vendidos AS v
        LEFT JOIN productos AS p ON p.id = v.idProducto
        LEFT JOIN cuentas_apartados AS ca ON v.idReferencia = ca.id
        LEFT JOIN ventas AS ve ON v.idReferencia = ve.id
        LEFT JOIN usuarios AS u ON ca.idUsuario = u.id OR ve.idUsuario = u.id
        LEFT JOIN clientes AS c ON ca.idCliente = c.id OR ve.idCliente = c.id
        LEFT JOIN proveedores AS pr ON p.proveedor = pr.id
        $whereVentas";

    // --- REMOVIDOS ---
    $whereRemovidos = " WHERE 1=1";
    $paramsRemovidos = [];
    if ($proveedor) {
        $whereRemovidos .= " AND pr.id = ?";
        $paramsRemovidos[] = $proveedor;
    }
    if ($productoId) {
        $whereRemovidos .= " AND r.idProducto = ?";
        $paramsRemovidos[] = $productoId;
    }
    if ($fechaInicio && $fechaFin) {
        $whereRemovidos .= " AND DATE(r.fecha) >= ? AND DATE(r.fecha) <= ?";
        $paramsRemovidos[] = $fechaInicio;
        $paramsRemovidos[] = $fechaFin;
    }
    $sentencia3 = "SELECT r.fecha, r.cantidad, NULL as monto,
        'Retiro' as tipo, p.nombre AS nombreProducto,
        u.usuario AS nombreUsuario, '-' AS signo, NULL as nombreCliente, pr.nombre AS nombreProveedor
        FROM productos_removidos AS r
        LEFT JOIN productos AS p ON p.id = r.idProducto
        LEFT JOIN usuarios AS u ON r.idUsuario = u.id
        LEFT JOIN proveedores AS pr ON p.proveedor = pr.id
        $whereRemovidos";

    $entradas = selectPrepare($sentencia1, $paramsEntradas);
    $salidas = selectPrepare($sentencia2, $paramsVentas);
    $removidos = selectPrepare($sentencia3, $paramsRemovidos);

    $movimientos = array_merge($entradas, $salidas, $removidos);

    // Ordenar por fecha descendente
    usort($movimientos, function($a, $b) {
        return strtotime($b->fecha) - strtotime($a->fecha);
    });

    return $movimientos;
}

function calcularGananciaInventario()
{
    $productos = obtenerExistencia();

    $total = 0;
    foreach ($productos as $producto) {
        $ganancia = ($producto->precioVenta - $producto->precioCompra) * $producto->existencia;
        $total += round($ganancia, 2);
    }

    return round($total, 2);
}

function calcularTotalInventario()
{
    $productos = obtenerExistencia();

    $total = 0;
    foreach ($productos as $producto) {
        $existencia = (float) $producto->existencia * (float) $producto->precioVenta;
        $total += (float) $existencia;
    }

    return $total;
}

function calcularNumeroTotalProductos()
{
    $productos = obtenerExistencia();
    $total = 0;

    foreach ($productos as $producto) {
        $total += $producto->existencia;
    }

    return $total;
}

function buscarProductoPorNombreOCodigo($producto)
{
    $sentencia = "SELECT * FROM productos WHERE (codigo = ? OR nombre LIKE ? OR codigo LIKE ?) LIMIT 10";
    $parametros = [$producto, '%' . $producto . '%', '%' . $producto . '%'];
    return selectPrepare($sentencia, $parametros);
}

function registrarProducto($producto)
{
    $sentencia = "INSERT INTO productos (codigo, nombre, unidad, precioCompra, precioVenta, precioVenta2, precioVenta3, vendidoMayoreo, precioMayoreo, cantidadMayoreo, marca, categoria, proveedor) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $parametros = [$producto->codigo, $producto->nombre, $producto->unidad, $producto->precioCompra, $producto->precioVenta, $producto->precioVenta2 ?? 0, $producto->precioVenta3 ?? 0, intval($producto->vendidoMayoreo), $producto->precioMayoreo, $producto->cantidadMayoreo, $producto->marca, $producto->categoria, $producto->proveedor];

    $resultado = insertar($sentencia, clean($parametros));

    if (floatval($producto->existencia) !== 0) {
        $idProducto = obtenerUltimoId('productos');

        $entrada = new stdClass;
        $entrada->cantidad = $producto->existencia;
        $entrada->monto = $producto->monto;
        $entrada->id = $idProducto;
        $entrada->usuario = $producto->usuario;

        $resultado = $resultado && agregarExistenciaProducto($entrada);
    }

    return $resultado;
}

function obtenerProductos()
{
    $sentencia = "SELECT productos.*, IFNULL(categorias.nombreCategoria, 'NO ENCONTRADA') AS nombreCategoria, IFNULL(marcas.nombreMarca, 'NO ENCONTRADA') AS nombreMarca, IFNULL(proveedores.nombre, 'NO ENCONTRADO') AS nombreProveedor
	FROM productos
	LEFT JOIN categorias ON categorias.id = productos.categoria
	LEFT JOIN marcas ON marcas.id = productos.marca
	LEFT JOIN proveedores ON proveedores.id = productos.proveedor";

    $productos = selectQuery($sentencia);
    $existencias = obtenerExistencia();

    foreach ($productos as $index => $producto) {
        $producto->existencia = $existencias[$index]->existencia;
    }

    return $productos;
}

function obtenerProductoPorId($id)
{
    $sentencia = "SELECT * FROM productos WHERE id = ?";
    $producto = selectRegresandoObjeto($sentencia, [$id]);
    $producto->existencia = obtenerExistencia($producto->id)->existencia;

    return $producto;
}

function editarProducto($producto)
{
    $sentencia = "UPDATE productos SET codigo = ?, nombre = ?, unidad = ?, precioCompra = ?, precioVenta = ?, precioVenta2 = ?, precioVenta3 = ?, vendidoMayoreo = ?, precioMayoreo = ?, cantidadMayoreo = ?, marca = ?, categoria = ?, proveedor = ? WHERE id = ?";

    $parametros = [$producto->codigo, $producto->nombre, $producto->unidad, $producto->precioCompra, $producto->precioVenta, $producto->precioVenta2, $producto->precioVenta3, intval($producto->vendidoMayoreo), $producto->precioMayoreo, $producto->cantidadMayoreo, $producto->marca, $producto->categoria, $producto->proveedor, $producto->id];

    return editar($sentencia, clean($parametros));
}

function eliminarProducto($id)
{
    $sentencia = "DELETE FROM productos WHERE id = ?";
    return eliminar($sentencia, $id);
}

/*

 __   __  _______  ______    _______ / _______  _______  _______  _______  _______ 
|  |_|  ||   _   ||    _ |  |       | |       ||   _   ||       ||       ||       |
|       ||  |_|  ||   | ||  |       | |       ||  |_|  ||_     _||    ___||    ___|
|       ||       ||   |_||_ |       | |       ||       |  |   |  |   |___ |   | __ 
|       ||       ||    __  ||      _| |      _||       |  |   |  |    ___||   ||  |
| ||_|| ||   _   ||   |  | ||     |_  |     |_ |   _   |  |   |  |   |___ |   |_| |
|_|   |_||__| |__||___|  |_||_______| |_______||__| |__|  |___|  |_______||_______|

*/
//FUNCIONES DE LAS MARCAS

function obtenerTotalesMarca()
{
    $sentencia = "SELECT marcas.nombreMarca, IFNULL(SUM(productos_vendidos.precio * productos_vendidos.cantidad), 0) AS totalVentas
	FROM productos_vendidos
	INNER JOIN productos ON productos.id = productos_vendidos.idProducto
	INNER JOIN marcas ON marcas.id = productos.marca
	WHERE productos_vendidos.tipo = 'venta'
	GROUP BY marcas.id";
    return selectQuery($sentencia);
}

function obtenerTotalesCategoria()
{
    $sentencia = "SELECT categorias.nombreCategoria, IFNULL(SUM(productos_vendidos.precio * productos_vendidos.cantidad), 0) AS totalVentas
	FROM productos_vendidos
	INNER JOIN productos ON productos.id = productos_vendidos.idProducto
	INNER JOIN categorias ON categorias.id = productos.categoria
	WHERE productos_vendidos.tipo = 'venta'
	GROUP BY categorias.id";
    return selectQuery($sentencia);
}

function registrarMarca($marca)
{
    $existe = verificarSiMarcaEstaRegistrada($marca->nombreMarca);
    if ($existe === 'true')
        return 'existe';

    $sentencia = "INSERT INTO marcas (nombreMarca) VALUES(?)";
    $parametros = [strtoupper($marca->nombreMarca)];
    return insertar($sentencia, $parametros);
}

function obtenerMarcas()
{
    $sentencia = "SELECT * FROM marcas";
    return selectQuery($sentencia);
}

function editarMarca($marca)
{
    $sentencia = "UPDATE marcas SET nombreMarca = ? WHERE id = ?";
    $parametros = [strtoupper($marca->nombreMarca), $marca->id];
    return editar($sentencia, $parametros);
}

function eliminarMarca($id)
{
    $sentencia = "DELETE FROM marcas WHERE id = ?";
    return eliminar($sentencia, $id);
}

function verificarSiMarcaEstaRegistrada($nombreMarca)
{
    $sentencia = "SELECT IF(  EXISTS(SELECT nombreMarca FROM marcas  WHERE nombreMarca = ? ),'true','false' ) AS resultado";
    return selectRegresandoObjeto($sentencia, [strtoupper($nombreMarca)])->resultado;
}

//FUNCIONES DE LAS CATEGORÍAS

function registrarCategoria($categoria)
{
    $existe = verificarSiCategoriaEstaRegistrada($categoria->nombreCategoria);
    if ($existe === 'true')
        return 'existe';

    $sentencia = "INSERT INTO categorias (nombreCategoria) VALUES(?)";
    $parametros = [strtoupper($categoria->nombreCategoria)];
    return insertar($sentencia, $parametros);
}

function obtenerCategorias()
{
    $sentencia = "SELECT * FROM categorias";
    return selectQuery($sentencia);
}

function editarCategoria($categoria)
{
    $sentencia = "UPDATE categorias SET nombreCategoria = ? WHERE id = ?";
    $parametros = [strtoupper($categoria->nombreCategoria), $categoria->id];
    return editar($sentencia, $parametros);
}

function eliminarCategoria($id)
{
    $sentencia = "DELETE FROM categorias WHERE id = ?";
    return eliminar($sentencia, $id);
}

function verificarSiCategoriaEstaRegistrada($nombreCategoria)
{
    $sentencia = "SELECT IF(  EXISTS(SELECT nombreCategoria FROM categorias  WHERE nombreCategoria = ? ),'true','false' ) AS resultado";
    return selectRegresandoObjeto($sentencia, [strtoupper($nombreCategoria)])->resultado;
}

function obtenerProductosDeVenta($id, $tipo)
{
    $sentencia = "SELECT *, CAST(productos_vendidos.cantidad AS DECIMAL(10,2)) AS cantidad
            FROM productos_vendidos
            LEFT JOIN productos ON productos.id = productos_vendidos.idProducto
            WHERE productos_vendidos.idReferencia = ? AND productos_vendidos.tipo = ?";
    $parametros = [$id, $tipo];
    $productos = selectPrepare($sentencia, $parametros);

    foreach ($productos as $producto) {
        $producto->cantidad = (float) $producto->cantidad;
    }

    return $productos;
}

function obtenerVenta($id)
{
    $sentencia = "SELECT
            ventas.id,
            ventas.fecha,
            ventas.total,
            ventas.pagado,
            ventas.simple,
            ventas.idMetodo,
            ventas.origen,
            deliveries.costo as costoDelivery,
            deliveries.gratis as deliveryGratis,
            deliveries.idChofer as deliveryId,
            metodos.nombre as nombreMetodo,
            IFNULL(clientes.nombre, 'MOSTRADOR') AS nombreCliente,
            IFNULL(usuarios.usuario, 'NO ENCONTRADO') AS nombreUsuario,
            clientes.telefono AS telefonoCliente,
            clientes.direccion AS direccionCliente,
            clientes.id AS clienteId
        FROM ventas
        LEFT JOIN clientes ON clientes.id = ventas.idCliente
        LEFT JOIN usuarios ON usuarios.id = ventas.idUsuario
        LEFT JOIN metodos ON metodos.id = ventas.idMetodo
        LEFT JOIN deliveries ON deliveries.idVenta = ventas.id
        LEFT JOIN productos_vendidos ON productos_vendidos.idReferencia = ventas.id
        WHERE ventas.id = ?";

    $venta = selectRegresandoObjeto($sentencia, [$id]);

    if ($venta && $venta->costoDelivery) {
        $venta->delivery = new stdClass;
        $venta->delivery->costo = $venta->costoDelivery;
        $venta->delivery->gratis = $venta->deliveryGratis;
        
        $sentenciaChoferes = "SELECT c.id, c.nombre FROM deliveries d JOIN choferes c ON d.idChofer = c.id WHERE d.idVenta = ?";
        $venta->delivery->choferes = selectPrepare($sentenciaChoferes, [$id]);
        
        unset($venta->costoDelivery);
        unset($venta->deliveryGratis);
    }

    if ($venta) {
        $venta->productos = obtenerProductosDeVenta($id, "venta");
        $venta->cliente = obtenerClientePorId($venta->clienteId);
    }

    return $venta;
}

function obtenerTotalAbonos($id)
{
    $sentencia = "SELECT SUM(monto) AS total FROM abonos WHERE idCuenta = ?";
    $total = (float) selectRegresandoObjeto($sentencia, [$id])->total;

    return $total;
}

function obtenerApartadoCuenta($id, $tipo)
{
    $sentencia = "SELECT
            cuentas.id,
            cuentas.fecha,
            cuentas.total,
            cuentas.dias,
            deliveries.costo as costoDelivery,
            deliveries.gratis as deliveryGratis,
            metodos.nombre as nombreMetodo,
            IFNULL(clientes.nombre, 'MOSTRADOR') AS nombreCliente,
            IFNULL(usuarios.usuario, 'NO ENCONTRADO') AS nombreUsuario,
            clientes.telefono AS telefonoCliente,
            clientes.direccion AS direccionCliente,
            clientes.id AS clienteId
        FROM cuentas_apartados as cuentas
        LEFT JOIN clientes ON clientes.id = cuentas.idCliente
        LEFT JOIN usuarios ON usuarios.id = cuentas.idUsuario
        LEFT JOIN deliveries ON deliveries.idCuenta = cuentas.id
        LEFT JOIN productos_vendidos ON productos_vendidos.idReferencia = cuentas.id
        LEFT JOIN abonos ON abonos.idCuenta = cuentas.id
        LEFT JOIN metodos ON metodos.id = abonos.idMetodo
        WHERE cuentas.id = ?";

    $venta = selectRegresandoObjeto($sentencia, [$id]);

    if ($venta && $venta->costoDelivery) {
        $venta->delivery = new stdClass;
        $venta->delivery->costo = $venta->costoDelivery;
        $venta->delivery->gratis = $venta->deliveryGratis;
        
        $sentenciaChoferes = "SELECT c.id, c.nombre FROM deliveries d JOIN choferes c ON d.idChofer = c.id WHERE d.idCuenta = ?";
        $venta->delivery->choferes = selectPrepare($sentenciaChoferes, [$id]);
        
        unset($venta->costoDelivery);
        unset($venta->deliveryGratis);
    }

    if ($venta) {
        $venta->productos = obtenerProductosDeVenta($id, $tipo);
        $venta->cliente = obtenerClientePorId($venta->clienteId);
        $venta->pagado = obtenerTotalAbonos($id);
    }

    return $venta;
}

/*

 ______   _______ 
|      | |  _    |
|  _    || |_|   |
| | |   ||       |
| |_|   ||  _   | 
|       || |_|   |
|______| |_______|

*/

function obtenerUltimoId($tabla)
{
    $bd = conectarBD();
    $sql = $bd->query("SELECT id FROM " . $tabla . " ORDER BY id DESC LIMIT 1");
    return $sql->fetchObject()->id;
}

function insertar($sentencia, $parametros)
{
    $bd = conectarBD();
    $sql = $bd->prepare($sentencia);
    return $sql->execute($parametros);
}

function editar($sentencia, $parametros)
{
    $bd = conectarBD();
    $sql = $bd->prepare($sentencia);
    return $sql->execute($parametros);
}

function eliminar($sentencia, $id)
{
    $bd = conectarBD();
    $sql = $bd->prepare($sentencia);
    $parametros = is_array($id) ? $id : [$id];
    return $sql->execute($parametros);
}

function selectRegresandoObjeto($sentencia, $parametros = [])
{
    $bd = conectarBD();
    $sql = $bd->prepare($sentencia);
    $sql->execute($parametros);
    return $sql->fetchObject();
}

function selectQuery($sentencia)
{
    $bd = conectarBD();
    $sql = $bd->query($sentencia);
    return $sql->fetchAll();
}

function selectPrepare($sentencia, $parametros)
{
    $bd = conectarBD();
    $sql = $bd->prepare($sentencia);
    $sql->execute($parametros);
    return $sql->fetchAll();
}

function conectarBD()
{
    $host = $_ENV['DB_HOST'];
    $db = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASSWORD'];
    $charset = $_ENV['DB_CHARSET'];

    $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    try {
        $pdo = new \PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int) $e->getCode());
    }
}

function dd($value)
{
    error_log(gettype($value) . ' ' . var_export($value, true), 4);
}

function clean($array)
{
    $copy = $array;

    foreach ($copy as $key => $value) {
        if ($value === '') {
            $copy[$key] = null;
        }
    }

    return $copy;
}
