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

define("FECHA_HOY", date("Y-m-d"));

define('DIRECTORIO', './logos/');

date_default_timezone_set('America/Caracas');

error_reporting(0);

function codificar($imagen) {
    $imagen = str_replace('data:image/png;base64,', '', $imagen);
    $imagen = str_replace('data:image/jpeg;base64,', '', $imagen);
    $imagen = str_replace(' ', '+', $imagen);
    $data = base64_decode($imagen);
    $file = DIRECTORIO. 'logo.png';
    $insertar = file_put_contents($file, $data);
    return $file;
}

function obtenerAjustes() {
	$sentencia = "SELECT * FROM configuracion";
	return selectRegresandoObjeto($sentencia);
}

function registrarAjustes($ajustes) {
	$logo = ($ajustes->cambiaLogo) ? codificar($ajustes->logo) : $ajustes->logo;
	$sentencia = (!obtenerAjustes()) ? 
	"INSERT INTO configuracion (nombre, telefono, logo) VALUES (?,?,?)" :
	"UPDATE configuracion SET nombre = ?, telefono = ?, logo = ?";

	$parametros = [$ajustes->nombre, $ajustes->telefono, $logo];
	return (!obtenerAjustes()) ? insertar($sentencia, $parametros) : editar($sentencia, $parametros);
}

function obtenerMetodos() {
	$sentencia = "SELECT * FROM metodos";
	return selectQuery($sentencia);
}

function obtenerMetodoPorId($id) {
	$sentencia = "SELECT * FROM metodos WHERE id = ?";
	return selectRegresandoObjeto($sentencia, [$id]);
}

function eliminarMetodo($id) {
	$sentencia = "DELETE FROM metodos WHERE id = ?";
	return eliminar($sentencia, $id);
}

function registrarMetodo($metodo) {
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

function editarMetodo($id, $metodo) {
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

function obtenerVentasPorDiaMes($mes, $anio) {
	$sentencia = "SELECT DATE_FORMAT(fecha, '%Y-%m-%d') AS dia, SUM(total) AS totalVentas FROM ventas 
	WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?
	GROUP BY dia";
	return selectPrepare($sentencia, [$mes, $anio]);
}

function obtenerTotalesVentasPorMes($anio) {
	$sentencia = "SELECT MONTH(fecha) AS mes, SUM(total) AS totalVentas FROM ventas 
        WHERE YEAR(fecha) = ? 
        GROUP BY MONTH(fecha) ORDER BY mes ASC";
    return selectPrepare($sentencia, [$anio]);
}

function calcularTotalIngresos() {
	$sentencia = "SELECT (SELECT SUM(total) FROM ventas) + (SELECT SUM(monto) FROM abonos) AS totalIngresos";
	return selectRegresandoObjeto($sentencia)->totalIngresos;
}

function calcularTotalIngresosHoy() {
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE DATE(fecha) = CURDATE()) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE DATE(fecha) = CURDATE()) AS totalIngresos";
	return selectRegresandoObjeto($sentencia)->totalIngresos;
}

function calcularTotalIngresosSemana() {
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE WEEK(fecha) = WEEK(NOW())) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE WEEK(fecha) = WEEK(NOW())) AS totalIngresos";
	return selectRegresandoObjeto($sentencia)->totalIngresos;
}

function calcularTotalIngresosMes() {
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())) AS totalIngresos";
	return selectRegresandoObjeto($sentencia)->totalIngresos;
}

function calcularIngresosPendientes() {
	$sentencia = "SELECT (
        (SELECT SUM(total) FROM cuentas_apartados)
        - (SELECT SUM(monto) FROM abonos)
    ) as pendientes";
	return selectRegresandoObjeto($sentencia)->pendientes;
}

function eliminarCotizacion($id) {
	$sentenciaEliminarCotizacion = "DELETE FROM cotizaciones WHERE id = ?";
	$cotizacionEliminada = eliminar($sentenciaEliminarCotizacion, $id);

	$sentenciaEliminarProductos = "DELETE FROM productos_cotizados WHERE idCotizacion = ?";
	$productosEliminados = eliminar($sentenciaEliminarProductos, $id);
	return $cotizacionEliminada && $productosEliminados;
}

function obtenerTotalVentas($filtros) {
	$sentencia = "SELECT SUM(total) AS totalVentas FROM ventas";
    $parametros = [];

    if ($filtros->fechaInicio && $filtros->fechaFin) {
        $sentencia .= " WHERE DATE(ventas.fecha) >= ? AND DATE(ventas.fecha) <= ?";
        $parametros = [$filtros->fechaInicio, $filtros->fechaFin];
    }

	return selectRegresandoObjeto($sentencia, $parametros)->totalVentas;
}

function obtenerTotalCuentasApartados($filtros, $tipo) {
	$sentencia = "SELECT SUM(total) AS total FROM cuentas_apartados WHERE tipo = ?";
	$parametros = [$tipo];

	if($filtros->fechaInicio && $filtros->fechaFin) {
		$sentencia .= " AND (DATE(cuentas_apartados.fecha) >= ? AND DATE(cuentas_apartados.fecha) <= ?)";
		array_push($parametros, $filtros->fechaInicio);
		array_push($parametros, $filtros->fechaFin);
	}
	return selectRegresandoObjeto($sentencia, $parametros)->total;
}

function obtenerTotalPorPagarCuentasApartados($filtros, $tipo) {
    $sentencia1 = "SELECT SUM(total) AS positivo FROM cuentas_apartados WHERE tipo = ?";
    $sentencia2 = "SELECT SUM(monto) AS negativo FROM abonos
        INNER JOIN cuentas_apartados
        ON cuentas_apartados.id = abonos.idCuenta
        AND cuentas_apartados.tipo = ?";
    
	$parametros = [$tipo];

	if($filtros->fechaInicio && $filtros->fechaFin) {
		$sentenciaFiltros = " AND (DATE(cuentas_apartados.fecha) >= ? AND DATE(cuentas_apartados.fecha) <= ?)";

        $sentencia1 .= $sentenciaFiltros;
        $sentencia2 .= $sentenciaFiltros;

		array_push($parametros, $filtros->fechaInicio);
		array_push($parametros, $filtros->fechaFin);
	}

	$positivo = selectRegresandoObjeto($sentencia1, $parametros)->positivo;
    $negativo = selectRegresandoObjeto($sentencia2, $parametros)->negativo;

    return $positivo - $negativo;
}

function obtenerPagosCuentasApartados($filtros, $tipo) {
	$sentencia = "SELECT SUM(monto) AS totalPagos FROM abonos
        INNER JOIN cuentas_apartados
        ON cuentas_apartados.id = abonos.idCuenta
        AND cuentas_apartados.tipo = ?";

	$parametros = [$tipo];

	if ($filtros->fechaInicio && $filtros->fechaFin) {
		$sentencia .= " AND (DATE(cuentas_apartados.fecha) >= ? AND DATE(cuentas_apartados.fecha) <= ?)";
		array_push($parametros, $filtros->fechaInicio);
		array_push($parametros, $filtros->fechaFin);
	}

	return selectRegresandoObjeto($sentencia, $parametros)->totalPagos;
}

function obtenerCuentasApartados($filtros, $tipo) {
	$sentencia = "SELECT cuentas_apartados.id, cuentas_apartados.fecha,
        cuentas_apartados.tipo, cuentas_apartados.total,
         cuentas_apartados.dias, SUM(abonos.monto) AS pagado,
        (cuentas_apartados.total - SUM(abonos.monto)) AS porPagar,
        IFNULL(clientes.nombre, 'MOSTRADOR') AS nombreCliente,
        IFNULL(clientes.telefono, '') AS telefonoCliente,
        IFNULL(usuarios.usuario, 'NO ENCONTRADO') AS nombreUsuario
		FROM cuentas_apartados
		LEFT JOIN clientes ON clientes.id = cuentas_apartados.idCliente
		LEFT JOIN usuarios ON usuarios.id = cuentas_apartados.idUsuario
        LEFT JOIN abonos ON abonos.idCuenta = cuentas_apartados.id
		WHERE cuentas_apartados.tipo = ? 
		GROUP BY cuentas_apartados.id
        ORDER BY cuentas_apartados.id DESC";

	$parametros = [$tipo];

	if ($filtros->fechaInicio && $filtros->fechaFin) {
		$sentencia .= " AND (DATE(cuentas_apartados.fecha) >= ? AND DATE(cuentas_apartados.fecha) <= ?)";
		array_push($parametros, $filtros->fechaInicio);
		array_push($parametros, $filtros->fechaFin);
	}
	$cuentas = selectPrepare($sentencia, $parametros);
	return agregarProductosVendidos($cuentas, $tipo);
}

function obtenerCotizaciones($filtros, $tipo) {
	$sentencia = "SELECT cotizaciones.id, cotizaciones.fecha, cotizaciones.total, cotizaciones.hasta, IFNULL(clientes.nombre, 'MOSTRADOR') AS nombreCliente, IFNULL(usuarios.usuario, 'NO ENCONTRADO') AS nombreUsuario 
		FROM cotizaciones
		LEFT JOIN clientes ON clientes.id = cotizaciones.idCliente
		LEFT JOIN usuarios ON usuarios.id = cotizaciones.idUsuario 
		WHERE 1";
	$parametros = [];

	if($filtros->fechaInicio) {
		$sentencia .= " AND (DATE(cotizaciones.fecha) >= ? AND DATE(cotizaciones.fecha) <= ?)";
		array_push($parametros, $filtros->fechaInicio);
		array_push($parametros, $filtros->fechaFin);
	}

	$cotizaciones = selectPrepare($sentencia, $parametros);
	return agregarProductosVendidos($cotizaciones, $tipo);
}


function obtenerVentas($filtros) {
	$fechaInicio = !$filtros->fechaInicio ? '1950-01-01' : $filtros->fechaInicio;
	$fechaFin = !$filtros->fechaFin ? '3000-12-31' : $filtros->fechaFin;

	$sentencia = "SELECT ventas.id, ventas.fecha, ventas.total, ventas.pagado, ventas.simple, ventas.idMetodo, ventas.origen, metodos.nombre as nombreMetodo, IFNULL(clientes.nombre, 'MOSTRADOR') AS nombreCliente, IFNULL(usuarios.usuario, 'NO ENCONTRADO') AS nombreUsuario 
		FROM ventas
		LEFT JOIN clientes ON clientes.id = ventas.idCliente
		LEFT JOIN usuarios ON usuarios.id = ventas.idUsuario
        LEFT JOIN metodos on metodos.id = ventas.idMetodo
		WHERE DATE(ventas.fecha) >= ? AND  DATE(ventas.fecha) <= ?
		ORDER BY ventas.id DESC";
	$parametros = [$fechaInicio, $fechaFin];
    // si este sistema dura más de 975 años activo, va a fallar XD
	$ventas =  selectPrepare($sentencia, $parametros);
	return agregarProductosVendidos($ventas, 'venta');
}

function agregarProductosVendidos($arreglo, $tipo) {
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

function obtenerProductosVendidos($id, $tipo) {
	$sentencia = "SELECT productos_vendidos.cantidad, productos_vendidos.precio, productos.nombre, productos.precioCompra, productos.id, productos.unidad
	FROM productos_vendidos
	LEFT JOIN productos ON productos.id =  productos_vendidos.idProducto
	WHERE productos_vendidos.idReferencia = ? AND productos_vendidos.tipo = ?";
	$parametros = [$id, $tipo];
	return selectPrepare($sentencia, $parametros);
}

function obtenerProductosCotizados($id) {
	$sentencia = "SELECT productos_cotizados.cantidad, productos_cotizados.precio, productos.nombre, productos.precioCompra, productos.id, productos.unidad
	FROM productos_cotizados
	LEFT JOIN productos ON productos.id =  productos_cotizados.idProducto
	WHERE productos_cotizados.idCotizacion = ?";
	$parametros = [$id];
	return selectPrepare($sentencia, $parametros);
}

function terminarVenta($venta) {
	$tipo = $venta->tipo;

	switch ($tipo) {
		case 'venta':
			return vender($venta);
			break;

		case 'cuenta':
			return agregarCuentaApartado($venta);
			break;

		case 'apartado':
			return agregarCuentaApartado($venta);
			break;

		case 'cotiza':
			return agregarCotizacion($venta);
			break;
		
		default:
			return false;
			break;
	}

}

function registrarDelivery($venta, $relacion, $id) {
    $delivery = $venta->delivery;

    if ($delivery->idChofer === '0') {
        $chofer = $venta->chofer;
        $sentencia = "INSERT INTO choferes (nombre, telefono, tipo, ci) VALUES (?,?,?,?)";
        $parametros = [$chofer->nombre, $chofer->telefono, $chofer->tipo, $chofer->ci];
        insertar($sentencia, clean($parametros));
        $delivery->idChofer = obtenerUltimoId('choferes');
    }

    $sentencia = "INSERT INTO deliveries (costo, destino, gratis, idChofer, $relacion) VALUES (?,?,?,?,?)";
    $parametros = [$delivery->costo, $delivery->destino, intval($delivery->gratis), $delivery->idChofer, $id];

    insertar($sentencia, clean($parametros));
}

function vender($venta) {
	$venta->cliente = (isset($venta->cliente)) ? $venta->cliente : 0;
	$sentencia = "INSERT INTO ventas (fecha, total, pagado, origen, `simple`, idMetodo, idCliente, idUsuario) VALUES (?,?,?,?,?,?,?,?)";

	$parametros = [date("Y-m-d H:i:s"), $venta->total, $venta->pagado, $venta->origen, $venta->simple, $venta->idMetodo, $venta->cliente, $venta->usuario];

    $registrado = insertar($sentencia, clean($parametros));
	
	if(!$registrado) return false;

	$idVenta = obtenerUltimoId('ventas');
	$productosRegistrados = registrarProductosVendidos($venta->productos, $idVenta, 'venta');

    $valido = count($productosRegistrados) > 0;
    if (!$valido) return false;

    if (!$venta->delivery) return $idVenta;

    registrarDelivery($venta, 'idVenta', $idVenta);

    return $idVenta;
}

function agregarCuentaApartado($venta) {
	$sentencia = "INSERT INTO cuentas_apartados (fecha, total, dias, tipo, idCliente, idUsuario) VALUES (?,?,?,?,?,?)";

    $ahora = date("Y-m-d H:i:s");
	$parametros = [$ahora, $venta->total, $venta->dias, $venta->tipo, $venta->cliente, $venta->usuario];

	$registrado = insertar($sentencia, clean($parametros));
	
	if(!$registrado) return false;
    
    $idCuenta = obtenerUltimoId('cuentas_apartados');

    if ($venta->pagado) {
        $sentencia = "INSERT INTO abonos (fecha, monto, origen, `simple`, idMetodo, idCuenta) VALUES (?,?,?,?,?,?)";

        $parametros = [$ahora, $venta->pagado, $venta->origen, $venta->simple, $venta->idMetodo, $idCuenta];

        insertar($sentencia, clean($parametros));
    }

    $productosRegistrados = registrarProductosVendidos($venta->productos, $idCuenta, $venta->tipo);

    $valido = count($productosRegistrados) > 0;
    if (!$valido) return false;

    if (!$venta->delivery) return $idCuenta;
    registrarDelivery($venta, 'idCuenta', $idCuenta);

    return $idCuenta;
}

function agregarCotizacion($venta) {
	$sentencia = "INSERT INTO cotizaciones(fecha, total, hasta, idCliente, idUsuario) VALUES (?,?,?,?,?)";
	$parametros = [date("Y-m-d H:i:s"), $venta->total, $venta->hasta, $venta->cliente, $venta->usuario];

	insertar($sentencia, clean($parametros));
	$idCotizacion = obtenerUltimoId('cotizaciones');

	$productosRegistrados = registrarProductosCotizados($venta->productos, $idCotizacion);

	if(count($productosRegistrados) > 0) return $idCotizacion;
}

function registrarProductosVendidos($productos, $idReferencia, $tipo) {
	$sentencia = "INSERT INTO productos_vendidos (fecha, cantidad, precio, idProducto, idReferencia, tipo) VALUES (?,?,?,?,?,?)";
	$resultados = [];

	foreach ($productos as $producto) {
		$parametros = [date('Y-m-d H:i:s'), $producto->cantidad, $producto->precio, $producto->id, $idReferencia, $tipo];
		$productoRegistrado = insertar($sentencia, $parametros);
		if($productoRegistrado) array_push($resultados, 1);
	}

	return $resultados;
}

function registrarProductosCotizados($productos, $idCotizacion) {
	$sentencia = "INSERT INTO productos_cotizados (cantidad, precio, idProducto, idCotizacion) VALUES(?,?,?,?)";
	$resultados = [];

	foreach ($productos as $producto) {
		$parametros = [$producto->cantidad, $producto->precio, $producto->id, $idCotizacion];
		$productoRegistrado = insertar($sentencia, $parametros);
		if($productoRegistrado) array_push($resultados, 1);
	}

	return $resultados;
}

function montoPorPagarCuentaApartado($id) {
    $sentencia = "SELECT (cuentas_apartados.total - SUM(abonos.monto)) AS porPagar
        FROM cuentas_apartados
        LEFT JOIN abonos ON cuentas_apartados.id = abonos.idCuenta
        WHERE cuentas_apartados.id = ?";

    return selectRegresandoObjeto($sentencia, [$id])->porPagar;
}

function obtenerCuentaApartado($id) {
    return selectRegresandoObjeto("SELECT cuentas_apartados.*,
        clientes.nombre AS nombreCliente,
        SUM(abonos.monto) AS pagado,
        (total - SUM(abonos.monto)) AS porPagar
        FROM cuentas_apartados
        LEFT JOIN clientes ON clientes.id = cuentas_apartados.idCliente
        LEFT JOIN abonos ON cuentas_apartados.id = abonos.idCuenta
        WHERE cuentas_apartados.id = ?", [$id]);
}

/* Abonos */

function obtenerAbonosPorCuentaApartado($id) {
	$abonos = selectPrepare("SELECT abonos.*, metodos.nombre AS metodo FROM abonos
        LEFT JOIN metodos ON abonos.idMetodo = metodos.id
        WHERE abonos.idCuenta = ?", [$id]);

    $cuentaApartado = obtenerCuentaApartado($id);

    if ($abonos === false || $cuentaApartado === false) return false;

    return ['abonos' => $abonos, 'cuentaApartado' => $cuentaApartado];
}

function registrarAbono($abono) {
    $sentencia = "INSERT INTO abonos (fecha, monto, origen, `simple`, idMetodo, idCuenta) VALUES (?,?,?,?,?,?)";

    $parametros = [date('Y-m-d H:i:s'), $abono->monto, $abono->origen, $abono->simple, $abono->idMetodo, $abono->idCuenta];

    insertar($sentencia, clean($parametros));
    return obtenerUltimoId('abonos');
}

/*                                                                                                  
 __   __  _______  __   __  _______  ______    ___   _______  _______ 
|  | |  ||       ||  | |  ||   _   ||    _ |  |   | |       ||       |
|  | |  ||  _____||  | |  ||  |_|  ||   | ||  |   | |   _   ||  _____|
|  |_|  || |_____ |  |_|  ||       ||   |_||_ |   | |  | |  || |_____ 
|       ||_____  ||       ||       ||    __  ||   | |  |_|  ||_____  |
|       | _____| ||       ||   _   ||   |  | ||   | |       | _____| |
|_______||_______||_______||__| |__||___|  |_||___| |_______||_______|
                                                                    
*/

// TODO -> los calculos de estadísticas de ventas (SUM(total) FROM ventas) deberían hacerse directo a la tabla "productos_vendidos" filtrando por tipo = venta, ya que el total de ventas puede incluir el delivery pagado por el cliente, que no sería una ganancia técnicamente
function obtenerTotalVentasPorMesUsuario($idUsuario, $anio) {
	$sentencia = "SELECT MONTH(fecha) AS mes, SUM(total) AS totalVentas FROM ventas 
        WHERE YEAR(fecha) = ?  AND idUsuario = ?
        GROUP BY MONTH(fecha) ORDER BY mes ASC";
    $parametros = [$anio, $idUsuario];
    return selectPrepare($sentencia, $parametros);
}

function calcularTotalIngresosUsuario($idUsuario) {
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE idUsuario = ?) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE idUsuario = ?) AS totalIngresos";
	$parametros = [$idUsuario, $idUsuario];
	return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function calcularTotalIngresosHoyUsuario($idUsuario) {
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE DATE(fecha) = CURDATE() AND idUsuario = ?) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE DATE(fecha) = CURDATE() AND idUsuario = ?) AS totalIngresos";
	$parametros = [$idUsuario, $idUsuario];
	return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function calcularTotalIngresosSemanaUsuario($idUsuario) {
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE WEEK(fecha) = WEEK(NOW()) AND idUsuario = ?) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE WEEK(fecha) = WEEK(NOW()) AND idUsuario = ?) AS totalIngresos";
	$parametros = [$idUsuario, $idUsuario];
	return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function calcularTotalIngresosMesUsuario($idUsuario) {
	$sentencia = "SELECT 
	(SELECT IFNULL(SUM(total),0) FROM ventas WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE()) AND idUsuario = ?) + 
	(SELECT IFNULL(SUM(monto),0) FROM abonos WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE()) AND idUsuario = ?) AS totalIngresos";
	$parametros = [$idUsuario, $idUsuario];
	return selectRegresandoObjeto($sentencia, $parametros)->totalIngresos;
}

function obtenerVentasPorUsuario() {
	$sentencia = "SELECT usuarios.usuario, SUM(ventas.total) AS totalVentas
    FROM ventas
	INNER JOIN usuarios ON usuarios.id = ventas.idUsuario
	GROUP BY usuarios.id";
	return selectQuery($sentencia);
}

function iniciarSesion($usuario) {
	$sentencia = "SELECT * FROM usuarios WHERE usuario = ?";
	$parametros = [$usuario->usuario];
	$resultado = selectRegresandoObjeto($sentencia, $parametros);
	if($resultado) {
		$loginCorrecto = verificarPassword($resultado->id, $usuario->password);
		if($loginCorrecto) {
			$datos = [
				"id" => $resultado->id,
				"usuario" => $resultado->usuario,
				"nombre" => $resultado->nombre
			];	

			return ["estado" => $loginCorrecto, "usuario" => $datos];
		}
	
	}
	return false;

}

function verificarPassword($idUsuario, $password) {
	$sentencia = "SELECT password FROM usuarios WHERE id = ?";
	$parametros = [$idUsuario];
	$resultado = selectRegresandoObjeto($sentencia, $parametros);
	$verificar = password_verify($password, $resultado->password);
	if($verificar) return true;
	return false;
}

function cambiarPassword($idUsuario, $password) {
	$sentencia = "UPDATE usuarios SET password = ? WHERE id = ?";
	$parametros = [$password, $idUsuario];
	return editar($sentencia, $parametros);
}
function registrarUsuario($usuario) {
	$sentencia = "INSERT INTO usuarios (usuario, nombre, telefono, password) VALUES (?,?,?,?)";
	$parametros = [$usuario->usuario, $usuario->nombre, $usuario->telefono, $usuario->password];
	return insertar($sentencia, $parametros);
}

function obtenerUsuarioPorId($id) {
	$sentencia = "SELECT id, usuario, nombre, telefono FROM usuarios WHERE id = ?";
	return selectRegresandoObjeto($sentencia, [$id]);
}

function editarUsuario($usuario) {
	$sentencia = "UPDATE usuarios SET usuario = ?, nombre = ?, telefono = ? WHERE id = ?";
	$parametros = [$usuario->usuario, $usuario->nombre, $usuario->telefono, $usuario->id];
	return editar($sentencia, $parametros);
}

function eliminarUsuario($id) {
	$sentencia = "DELETE FROM usuarios WHERE id = ?";
	return eliminar($sentencia, $id);
}

function obtenerUsuarios() {
	$sentencia = "SELECT id, usuario, nombre, telefono FROM usuarios";
	return selectQuery($sentencia);
}

/*

 _______  ___      ___   _______  __    _  _______  _______  _______ 
|       ||   |    |   | |       ||  |  | ||       ||       ||       |
|       ||   |    |   | |    ___||   |_| ||_     _||    ___||  _____|
|       ||   |    |   | |   |___ |       |  |   |  |   |___ | |_____ 
|      _||   |___ |   | |    ___||  _    |  |   |  |    ___||_____  |
|     |_ |       ||   | |   |___ | | |   |  |   |  |   |___  _____| |
|_______||_______||___| |_______||_|  |__|  |___|  |_______||_______|

*/

function obtenerVentasPorCliente() {
	$sentencia = "SELECT clientes.nombre, SUM(ventas.total) AS totalVentas  FROM ventas
	INNER JOIN clientes ON clientes.id = ventas.idCliente
	GROUP BY clientes.id";
	return selectQuery($sentencia);
}

function obtenerClientesPorNombre($nombre) {
	$sentencia = "SELECT * FROM clientes WHERE nombre LIKE ?";
	$parametros = ["%".$nombre."%"];
	return selectPrepare($sentencia, $parametros);
}

function obtenerClientes() {
	$sentencia = "SELECT * FROM clientes";
	return selectQuery($sentencia);
}

function registrarCliente($cliente) {
	$sentencia = "INSERT INTO clientes (nombre, telefono, tipo, ci) VALUES (?,?,?,?)";
	$parametros = [$cliente->nombre, $cliente->telefono, $cliente->tipo, $cliente->ci];
	return insertar($sentencia, clean($parametros));
}

function obtenerClientePorId($id) {
	$sentencia = "SELECT * FROM clientes WHERE id = ?";
	return selectRegresandoObjeto($sentencia, [$id]);
}

function editarCliente($cliente) {
	$sentencia = "UPDATE clientes SET nombre = ?, telefono = ?, tipo = ?, ci = ? WHERE id = ?";
	$parametros = [$cliente->nombre, $cliente->telefono, $cliente->tipo, $cliente->ci, $cliente->id];
	return editar($sentencia, $parametros);
}

function eliminarCliente($id) {
	$sentencia = "DELETE FROM clientes WHERE id = ?";
	return eliminar($sentencia, $id);
}


/* Choferes */
function obtenerChoferes() {
    $pagado = "SELECT IFNULL(SUM(monto),0) FROM pagos WHERE pagos.idChofer = choferes.id)";
	$sentencia = "SELECT choferes.*,
        (IFNULL(SUM(deliveries.costo),0) - ($pagado) as deuda
        FROM choferes
        LEFT JOIN deliveries ON deliveries.idChofer = choferes.id
        GROUP BY choferes.id;";
	return selectQuery($sentencia);
}

function obtenerChoferesPorNombre($nombre) {
    $pagado = "SELECT IFNULL(SUM(monto),0) FROM pagos WHERE pagos.idChofer = choferes.id)";
	$sentencia = "SELECT choferes.*,
        (IFNULL(SUM(deliveries.costo),0) - ($pagado)) as deuda
        FROM choferes
        LEFT JOIN deliveries ON deliveries.idChofer = choferes.id
        LEFT JOIN pagos ON pagos.idChofer = choferes.id
        WHERE choferes.nombre LIKE ?
        GROUP BY choferes.id;";
	$parametros = ["%".$nombre."%"];
	return selectPrepare($sentencia, $parametros);
}

function obtenerChoferPorId($id) {
    $pagado = "SELECT IFNULL(SUM(monto),0) FROM pagos WHERE pagos.idChofer = choferes.id)";
	$sentencia = "SELECT choferes.*,
        (IFNULL(SUM(deliveries.costo),0) - ($pagado)) as deuda
        FROM choferes
        LEFT JOIN deliveries ON deliveries.idChofer = choferes.id
        LEFT JOIN pagos ON pagos.idChofer = choferes.id
        WHERE choferes.id = ?
        GROUP BY choferes.id;";
	return selectRegresandoObjeto($sentencia, [$id]);
}

function editarChofer($chofer) {
	$sentencia = "UPDATE choferes SET nombre = ?, telefono = ?, tipo = ?, ci = ? WHERE id = ?";
	$parametros = [$chofer->nombre, $chofer->telefono, $chofer->tipo, $chofer->ci, $chofer->id];
	return editar($sentencia, $parametros);
}

function registrarPagoChofer($pago) {
    $sentencia = "INSERT INTO pagos (monto, idChofer) VALUES (?,?)";
    $parametros = [$pago->monto, $pago->idChofer];
    return insertar($sentencia, $parametros);
}

function obtenerDeliveries() {
	$sentencia = "SELECT deliveries.*, choferes.nombre as nombreChofer
        FROM deliveries
        LEFT JOIN choferes ON deliveries.idChofer = choferes.id;";

	return selectQuery($sentencia);
}

/* PROVEEDORES */

function obtenerProveedores() {
	$sentencia = "SELECT * FROM proveedores;";
	return selectQuery($sentencia);
}

function obtenerProveedorPorId($id) {
	$sentencia = "SELECT * FROM proveedores WHERE id = ?;";
	return selectRegresandoObjeto($sentencia, [$id]);
}

function registrarProveedor($datos) {
	$sentencia = "INSERT INTO proveedores (nombre, telefono, rif, direccion) VALUES (?,?,?,?);";
    $parametros = [$datos->nombre, $datos->telefono, $datos->rif, $datos->direccion];
	return insertar($sentencia, $parametros);
}

function editarProveedor($datos) {
	$sentencia = "UPDATE proveedores SET nombre = ?, telefono = ?, rif = ?, direccion = ? WHERE id = ?";
	$parametros = [$datos->nombre, $datos->telefono, $datos->rif, $datos->direccion, $datos->id];
	return editar($sentencia, $parametros);
}

/*

 _______  ______    _______  ______   __   __  _______  _______  _______  _______ 
|       ||    _ |  |       ||      | |  | |  ||       ||       ||       ||       |
|    _  ||   | ||  |   _   ||  _    ||  | |  ||       ||_     _||   _   ||  _____|
|   |_| ||   |_||_ |  | |  || | |   ||  |_|  ||       |  |   |  |  | |  || |_____ 
|    ___||    __  ||  |_|  || |_|   ||       ||      _|  |   |  |  |_|  ||_____  |
|   |    |   |  | ||       ||       ||       ||     |_   |   |  |       | _____| |
|___|    |___|  |_||_______||______| |_______||_______|  |___|  |_______||_______|

*/
function obtenerProductosMasVendidos($limite) {
	$sentencia = "SELECT SUM(productos_vendidos.cantidad * productos_vendidos.precio) AS total, SUM(productos_vendidos.cantidad) AS unidades,
	productos.nombre
    FROM productos_vendidos INNER JOIN productos ON productos.id = productos_vendidos.idProducto
	WHERE productos_vendidos.tipo = 'venta'
	GROUP BY productos_vendidos.idProducto
	ORDER BY total DESC
	LIMIT ?";
	return selectPrepare($sentencia, [$limite]);
}

function obtenerExistencia($id = null) {
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

function agregarExistenciaProducto($entrada) {
	$sentencia = "INSERT INTO entradas (fecha, cantidad, idProducto, idUsuario) VALUES (?,?,?,?)";
	$parametros = [date('Y-m-d H:i:s'), $entrada->cantidad, $entrada->id, $entrada->usuario];
	return insertar($sentencia, $parametros);
}

function removerExistenciaProducto($producto) {
	$sentencia = "INSERT INTO productos_removidos (fecha, cantidad, idProducto, idUsuario) VALUES (?,?,?,?)";
	$parametros = [date('Y-m-d H:i:s'), $producto->cantidad, $producto->id, $producto->usuario];
	return insertar($sentencia, $parametros);
}

function obtenerHistorialInventario() {
    $sentencia1 = "SELECT e.fecha, e.cantidad,
        p.nombre AS nombreProducto, u.usuario AS nombreUsuario,
        IF((SELECT MIN(e1.fecha) FROM entradas AS e1 WHERE e1.idProducto = e.idProducto) = e.fecha, 'Registro', 'Reposición') AS tipo, '+' AS signo
        FROM entradas AS e
        LEFT JOIN productos AS p ON p.id = e.idProducto
        LEFT JOIN usuarios AS u ON e.idUsuario = u.id;";

    $sentencia2 = "SELECT v.fecha, v.cantidad,
        'Venta' AS tipo, p.nombre AS nombreProducto,
        u.usuario AS nombreUsuario, '-' AS signo,
        c.nombre AS nombreCliente
        FROM productos_vendidos AS v
        LEFT JOIN productos AS p ON p.id = v.idProducto
        LEFT JOIN cuentas_apartados AS ca ON v.idReferencia = ca.id
        LEFT JOIN ventas AS ve ON v.idReferencia = ve.id
        LEFT JOIN usuarios AS u ON ca.idUsuario = u.id OR ve.idUsuario = u.id
        LEFT JOIN clientes AS c ON ca.idCliente = c.id OR ve.idCliente = c.id;";

    $sentencia3 = "SELECT r.fecha, r.cantidad,
        'Retiro' as tipo, p.nombre AS nombreProducto,
        u.usuario AS nombreUsuario, '-' AS signo
        FROM productos_removidos AS r
        LEFT JOIN productos AS p ON p.id = r.idProducto
        LEFT JOIN usuarios AS u ON r.idUsuario = u.id;";

    $entradas = selectQuery($sentencia1);
    $salidas = selectQuery($sentencia2);
    $removidos = selectQuery($sentencia3);

    $movimientos = array_merge($entradas, $salidas, $removidos);

    return $movimientos;
}

function calcularGananciaInventario() {
    $productos = obtenerExistencia();

    $total = 0;
    foreach ($productos as $producto) {
        $positivo = (float) $producto->existencia * (float) $producto->precioVenta;
        $negativo = (float) $producto->existencia * (float) $producto->precioCompra;
        $total += (float) $positivo - $negativo;
    }

    return $total;
}

function calcularTotalInventario() {
    $productos = obtenerExistencia();

    $total = 0;
    foreach ($productos as $producto) {
        $existencia = (float) $producto->existencia * (float) $producto->precioVenta;
        $total += (float) $existencia;
    }

    return $total;
}

function calcularNumeroTotalProductos() {
	$productos = obtenerExistencia();
    $total = 0;

    foreach ($productos as $producto) {
        $total += $producto->existencia;
    }

	return $total;
}

function buscarProductoPorNombreOCodigo($producto) {
	$sentencia = "SELECT * FROM productos WHERE (codigo = ? OR nombre LIKE ? OR codigo LIKE ?) LIMIT 10";
	$parametros = [$producto, '%'.$producto.'%', '%'.$producto.'%'];
	return selectPrepare($sentencia, $parametros);
}

function registrarProducto($producto) {
    $sentencia = "INSERT INTO productos (codigo, nombre, unidad, precioCompra, precioVenta, precioVenta2, precioVenta3, vendidoMayoreo, precioMayoreo, cantidadMayoreo, marca, categoria, proveedor) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $parametros = [$producto->codigo, $producto->nombre, $producto->unidad, $producto->precioCompra, $producto->precioVenta, $producto->precioVenta2, $producto->precioVenta3, intval($producto->vendidoMayoreo), $producto->precioMayoreo, $producto->cantidadMayoreo, $producto->marca, $producto->categoria, $producto->proveedor];

    $resultado = insertar($sentencia, clean($parametros));

    if (floatval($producto->existencia) !== 0) {
        $idProducto = obtenerUltimoId('productos');
        $resultado = $resultado && agregarExistenciaProducto($producto->existencia, $idProducto);
    }

    return $resultado;
}

function obtenerProductos() {
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

function obtenerProductoPorId($id) {
	$sentencia = "SELECT * FROM productos WHERE id = ?";
	$producto = selectRegresandoObjeto($sentencia, [$id]);
    $producto->existencia = obtenerExistencia($producto->id)->existencia;

    return $producto;
}

function editarProducto($producto) {
	$sentencia = "UPDATE productos SET codigo = ?, nombre = ?, unidad = ?, precioCompra = ?, precioVenta = ?, precioVenta2 = ?, precioVenta3 = ?, vendidoMayoreo = ?, precioMayoreo = ?, cantidadMayoreo = ?, marca = ?, categoria = ?, proveedor = ? WHERE id = ?";

    $parametros = [$producto->codigo, $producto->nombre, $producto->unidad, $producto->precioCompra, $producto->precioVenta, $producto->precioVenta2, $producto->precioVenta3, intval($producto->vendidoMayoreo), $producto->precioMayoreo, $producto->cantidadMayoreo, $producto->marca, $producto->categoria, $producto->proveedor, $producto->id];

	return editar($sentencia, clean($parametros));
}

function eliminarProducto($id) {
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

function obtenerTotalesMarca() {
	$sentencia = "SELECT marcas.nombreMarca, SUM(productos_vendidos.precio * productos_vendidos.cantidad) AS totalVentas
	FROM productos_vendidos
	INNER JOIN productos ON productos.id = productos_vendidos.idProducto
	INNER JOIN marcas ON marcas.id = productos.marca
	WHERE productos_vendidos.tipo = 'venta'
	GROUP BY marcas.id";
	return selectQuery($sentencia);
}

function obtenerTotalesCategoria() {
	$sentencia = "SELECT categorias.nombreCategoria, SUM(productos_vendidos.precio * productos_vendidos.cantidad) AS totalVentas
	FROM productos_vendidos
	INNER JOIN productos ON productos.id = productos_vendidos.idProducto
	INNER JOIN categorias ON categorias.id = productos.categoria
	WHERE productos_vendidos.tipo = 'venta'
	GROUP BY categorias.id";
	return selectQuery($sentencia);
}

function registrarMarca($marca) {
	$existe = verificarSiMarcaEstaRegistrada($marca->nombreMarca);
	if($existe === 'true') return 'existe';

	$sentencia = "INSERT INTO marcas (nombreMarca) VALUES(?)";
	$parametros = [strtoupper($marca->nombreMarca)];
	return insertar($sentencia, $parametros);
}

function obtenerMarcas() {
	$sentencia = "SELECT * FROM marcas";
	return selectQuery($sentencia);
}

function editarMarca($marca) {
	$sentencia = "UPDATE marcas SET nombreMarca = ? WHERE id = ?";
	$parametros = [strtoupper($marca->nombreMarca), $marca->id];
	return editar($sentencia, $parametros);
}

function eliminarMarca($id) {
	$sentencia = "DELETE FROM marcas WHERE id = ?";
	return eliminar($sentencia, $id);
}

function verificarSiMarcaEstaRegistrada($nombreMarca) {
	$sentencia = "SELECT IF(  EXISTS(SELECT nombreMarca FROM marcas  WHERE nombreMarca = ? ),'true','false' ) AS resultado";
	return selectRegresandoObjeto($sentencia, [strtoupper($nombreMarca)])->resultado;
}

//FUNCIONES DE LAS CATEGORÍAS

function registrarCategoria($categoria) {
	$existe = verificarSiCategoriaEstaRegistrada($categoria->nombreCategoria);
	if($existe === 'true') return 'existe';

	$sentencia = "INSERT INTO categorias (nombreCategoria) VALUES(?)";
	$parametros = [strtoupper($categoria->nombreCategoria)];
	return insertar($sentencia, $parametros);
}

function obtenerCategorias() {
	$sentencia = "SELECT * FROM categorias";
	return selectQuery($sentencia);
}

function editarCategoria($categoria) {
	$sentencia = "UPDATE categorias SET nombreCategoria = ? WHERE id = ?";
	$parametros = [strtoupper($categoria->nombreCategoria), $categoria->id];
	return editar($sentencia, $parametros);
}

function eliminarCategoria($id) {
	$sentencia = "DELETE FROM categorias WHERE id = ?";
	return eliminar($sentencia, $id);
}

function verificarSiCategoriaEstaRegistrada($nombreCategoria) {
	$sentencia = "SELECT IF(  EXISTS(SELECT nombreCategoria FROM categorias  WHERE nombreCategoria = ? ),'true','false' ) AS resultado";
	return selectRegresandoObjeto($sentencia, [strtoupper($nombreCategoria)])->resultado;
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

function obtenerUltimoId($tabla) {
	$bd = conectarBD();
	$sql = $bd->query("SELECT id FROM ".  $tabla ." ORDER BY id DESC LIMIT 1");
	return $sql->fetchObject()->id;
}

function insertar($sentencia, $parametros) {
	$bd = conectarBD();
	$sql = $bd->prepare($sentencia);
	return $sql->execute($parametros);
}

function editar($sentencia, $parametros) {
	$bd = conectarBD();
	$sql = $bd->prepare($sentencia);
	return $sql->execute($parametros);
}

function eliminar($sentencia, $id) {
	$bd = conectarBD();
	$sql = $bd->prepare($sentencia);
	return $sql->execute([$id]);
}

function selectRegresandoObjeto($sentencia, $parametros = []) {
	$bd = conectarBD();
	$sql = $bd->prepare($sentencia);
	$sql->execute($parametros);
	return $sql->fetchObject();
}

function selectQuery($sentencia) {
	$bd = conectarBD();
	$sql = $bd->query($sentencia);
	return $sql->fetchAll();
}

function selectPrepare($sentencia, $parametros) {
	$bd = conectarBD();
	$sql = $bd->prepare($sentencia);
	$sql->execute($parametros);
	return $sql->fetchAll();
}

function conectarBD() {
 	$host = $_ENV['DB_HOST'];
	$db   = $_ENV['DB_NAME'];
	$user = $_ENV['DB_USER'];
	$pass = $_ENV['DB_PASSWORD'];
	$charset = $_ENV['DB_CHARSET'];

	$options = [
	    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
	    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
	    \PDO::ATTR_EMULATE_PREPARES   => false,
	];
	$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
	try {
	     $pdo = new \PDO($dsn, $user, $pass, $options);
	     return $pdo;
	} catch (\PDOException $e) {
	     throw new \PDOException($e->getMessage(), (int)$e->getCode());
	}
 }

function dd($value) {
    error_log(gettype($value) . ' ' . var_export($value, true), 4);
}

function clean($array) {
    $copy = $array;

    foreach ($copy as $key => $value) {
        if ($value === '') {
            $copy[$key] = null;
        }
    }

    return $copy;
}
