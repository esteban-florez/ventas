<?php

include_once "encabezado.php";
$payload = json_decode(file_get_contents("php://input"));
if (!$payload) {
	http_response_code(500);
	exit;
}

include_once "funciones.php";
include_once "auth.php";

$accion = $payload->accion;

switch ($accion) {
	case 'registrar_venta':
		echo json_encode(vender($payload->datos));
		break;

	case 'registrar_cuenta':
		echo json_encode(agregarCuentaApartado($payload->datos));
		break;

	case 'registrar_apartado':
		echo json_encode(agregarCuentaApartado($payload->datos));
		break;

	case 'registrar_cotiza':
		echo json_encode(agregarCotizacion($payload->datos));
		break;

	case 'obtener_ventas':
		echo json_encode(
			[
				"totalVentas" => obtenerTotalVentas($payload->filtros),
				"ventas" => obtenerVentas($payload->filtros),
				"ventasFiltradas" => obtenerVentasFiltradas($payload->filtros)
			]
		);
		break;

	case 'obtener_cuentas':
		echo json_encode(
			[
				"totalPagos" => obtenerPagosCuentasApartados($payload->filtros, 'cuenta'),
				"totalCuentas" => obtenerTotalCuentasApartados($payload->filtros, 'cuenta'),
				"totalPorPagar" => obtenerTotalPorPagarCuentasApartados($payload->filtros, 'cuenta'),
				"cuentas" => obtenerCuentasApartados($payload->filtros, 'cuenta'),
				"cuentasFiltradas" => obtenerCuentasApartadosFiltrados($payload->filtros, 'cuenta')
			]
		)
		;
		break;

	case 'obtener_apartados':
		echo json_encode(
			[
				"totalPagos" => obtenerPagosCuentasApartados($payload->filtros, 'apartado'),
				"totalApartados" => obtenerTotalCuentasApartados($payload->filtros, 'apartado'),
				"totalPorPagar" => obtenerTotalPorPagarCuentasApartados($payload->filtros, 'apartado'),
				"apartados" => obtenerCuentasApartados($payload->filtros, 'apartado'),
				"apartadosFiltrados" => obtenerCuentasApartadosFiltrados($payload->filtros, 'apartado')
			]
		);
		break;

	case 'obtener_cotizaciones':
		echo json_encode(
			[
				"cotizaciones" => obtenerCotizaciones($payload->filtros, 'cotiza')
			]
		);
		break;

	case 'eliminar_cotiza':
		echo json_encode(eliminarCotizacion($payload->id));
		break;

	case 'por_pagar':
		echo json_encode(montoPorPagarCuentaApartado($payload->id));
		break;

	case 'obtener_cuenta_id':
		echo json_encode(obtenerCuentaUsandoAbono($payload->id));
		break;

	case 'editar_abono':
		echo json_encode(editarAbono($payload->id));
		break;

	case 'obtener_abonos':
		echo json_encode(obtenerAbonosPorCuentaApartado($payload->id));
		break;

	case 'realizar_abono':
		echo json_encode(registrarAbono($payload->abono));
		break;

	case 'obtener_todos_abonos':
		echo json_encode(obtenerTodosLosAbonosFiltrados($payload->filtros));
		break;

	case 'historial':
		echo json_encode(obtenerHistorialInventario($payload->proveedor));
		break;

	case 'eliminar_venta':
		echo json_encode(eliminarVenta($payload->id));
		break;

	case 'eliminar_cuenta':
		echo json_encode(eliminarCuentaApartado($payload->id));
		break;

	case 'obtener_venta':
		echo json_encode(obtenerVenta($payload->id));
		break;

	case 'actualizar_venta':
		echo json_encode(editarVenta($payload->id, $payload->datos));
		break;

	case 'actualizar_cuenta':
		echo json_encode(editarApartadoCuenta($payload->id, $payload->datos, $payload->tipo));
		break;

	case 'obtener_cuenta_apartado':
		echo json_encode(obtenerApartadoCuenta($payload->id, $payload->tipo));
		break;

	case 'actualizar_abono':
		echo json_encode(actualizarAbono($payload->abono));
		break;

	case 'eliminar_abono':
		echo json_encode(eliminarAbono($payload->id));
		break;

	default:
		echo json_encode("No se reconoce");
		break;
}
