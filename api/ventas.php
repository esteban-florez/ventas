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
				"ventas" => obtenerVentas($payload->filtros)
			]
		);
		break;

	case 'obtener_cuentas':
		echo json_encode(
			[
				"totalPagos" => obtenerPagosCuentasApartados($payload->filtros,'cuenta'),
				"totalCuentas" => obtenerTotalCuentasApartados($payload->filtros,'cuenta'),
				"totalPorPagar" => obtenerTotalPorPagarCuentasApartados($payload->filtros,'cuenta'),
				"cuentas" => obtenerCuentasApartados($payload->filtros, 'cuenta')
			])
		;
		break;

	case 'obtener_apartados':
		echo json_encode(
			[
				"totalPagos" => obtenerPagosCuentasApartados($payload->filtros,'apartado'),
				"totalApartados" => obtenerTotalCuentasApartados($payload->filtros,'apartado'),
				"totalPorPagar" => obtenerTotalPorPagarCuentasApartados($payload->filtros,'apartado'),
				"apartados" => obtenerCuentasApartados($payload->filtros, 'apartado')
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

    case 'obtener_abonos':
        echo json_encode(obtenerAbonosPorCuentaApartado($payload->id));
        break;

    case 'realizar_abono':
        echo json_encode(registrarAbono($payload->abono));
        break;

    case 'historial':
        echo json_encode(obtenerHistorialInventario($payload->proveedor));
        break;

	default:
		echo json_encode("No se reconoce");
		break;
}
