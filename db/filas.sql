-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 21, 2025 at 04:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ventas`
--

--
-- Dumping data for table `abonos`
--

INSERT INTO `abonos` (`id`, `fecha`, `monto`, `origen`, `simple`, `idMetodo`, `idCuenta`) VALUES
(1, '2025-02-28', 102.00, '04151231234', NULL, 1, 1),
(2, '2025-02-28', 1000.00, '02001234090012348080', NULL, 3, 2),
(3, '2025-02-28', 900.00, 'zelle123@ejemplo.com', NULL, 4, 3),
(4, '2025-02-28', 500.00, 'binance123@ejemplo.com', NULL, 5, 4),
(5, '2025-02-28', 22.00, NULL, 'Punto de Venta', NULL, 5);

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nombreCategoria`) VALUES
(1, 'Libretas'),
(2, 'Lapices'),
(3, 'Colores'),
(4, 'Plumones'),
(5, 'Crayolas'),
(8, 'Artículos de Oficina');

--
-- Dumping data for table `choferes`
--

INSERT INTO `choferes` (`id`, `nombre`, `telefono`, `tipo`, `ci`) VALUES
(1, 'Adrían Díaz', '04153940124', 'Venezolano', '22147159'),
(2, 'María Pérez', '04157584900', 'Venezolano', '15592050'),
(3, 'Juan González', '04150988907', 'Venezolano', '18291293'),
(4, 'Luisa Medina', '04151234123', 'Venezolano', '31424898'),
(5, 'Pedro Vargas', '04153941234', 'Venezolano', '14489391');

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `telefono`, `tipo`, `ci`, `direccion`) VALUES
(1, 'Carlos Rivera', '04128970019', 'Venezolano', '24145159', 'Calle 23, Casa 12, Maracay'),
(2, 'David Gonzalez', '04128970019', 'Venezolano', '17592050', 'Calle 27, Casa 40, Caracas'),
(3, 'Allan Primera', '', 'Venezolano', '8241293', 'Calle 45, Casa 70, Valencia'),
(4, 'Sebastian Laya', '04128970019', 'Venezolano', '19424098', 'Calle 58, Casa 14, Santa Cruz'),
(5, 'Myriam Hernandez', '', 'Venezolano', '10482391', 'Calle 12, Casa 25, Cagua');

--
-- Dumping data for table `cotizaciones`
--

INSERT INTO `cotizaciones` (`id`, `fecha`, `total`, `hasta`, `idCliente`, `idUsuario`) VALUES
(1, '2022-07-03 16:33:23', 1162.00, '2022-07-13', 2, 1),
(2, '2022-07-03 16:36:43', 1000.00, '2022-07-09', 1, 1),
(3, '2022-07-06 14:33:04', 4518.00, '2022-07-28', 2, 1),
(4, '2022-07-06 18:10:45', 1342.00, '2022-07-15', 2, 1),
(5, '2022-08-01 09:54:23', 6594.00, '2022-08-15', 4, 1);

--
-- Dumping data for table `cuentas_apartados`
--

INSERT INTO `cuentas_apartados` (`id`, `fecha`, `total`, `dias`, `tipo`, `idCliente`, `idUsuario`) VALUES
(1, '2025-02-18 17:30:03', 1002.00, 7, 'cuenta', 2, 1),
(2, '2025-02-19 17:36:09', 1000.00, 0, 'apartado', 1, 1),
(3, '2025-02-19 17:53:10', 1010.50, 10, 'cuenta', 1, 1),
(4, '2025-02-19 17:53:25', 1000.00, 0, 'apartado', 2, 1),
(5, '2025-02-19 17:53:15', 22.00, 15, 'cuenta', 1, 1);

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `costo`, `destino`, `gratis`, `idChofer`, `idVenta`, `idCuenta`) VALUES
(1, 4.00, 'Calle 25, Casa 10, Barrio Ejemplo, Valencia, Carabobo', 1, 1, 1, NULL),
(2, 2.00, 'Calle 50, Casa 42, Barrio Ejemplo, Maracay, Aragua', 1, 2, NULL, 1),
(3, 1.50, 'Calle 61, Casa 84, Barrio Ejemplo, Caracas, Dtto Capital', 0, 3, 2, NULL),
(4, 10.50, 'Calle 22, Casa 63, Barrio Ejemplo, Maracaibo, Zulia', 0, 4, NULL, 3),
(5, 9.50, 'Calle 40, Casa 37, Barrio Ejemplo, Cagua, Aragua', 1, 5, 3, NULL);

--
-- Dumping data for table `entradas`
--

INSERT INTO `entradas` (`id`, `fecha`, `monto`, `cantidad`, `idProducto`, `idUsuario`) VALUES
(1, '2025-01-02 11:00:00', 12500.00, 25.00, 1, 1),
(2, '2025-01-01 11:00:00', 1485.00, 99.00, 2, 1),
(3, '2025-01-03 11:00:00', 990.00, 66.00, 3, 1),
(4, '2025-01-01 11:00:00', 2265.00, 453.00, 4, 1),
(5, '2025-01-01 11:00:00', 4800.00, 96.00, 5, 1),
(6, '2025-01-03 11:00:00', 52080.00, 496.00, 6, 1),
(7, '2025-01-01 11:00:00', 275.00, 11.00, 7, 1),
(8, '2025-01-02 11:00:00', 4.62, 3.00, 8, 1),
(9, '2025-01-03 13:00:00', 15000.00, 30.00, 1, 1);

--
-- Dumping data for table `marcas`
--

INSERT INTO `marcas` (`id`, `nombreMarca`) VALUES
(1, 'Norma'),
(2, 'Scribe'),
(3, 'Maped'),
(4, 'Pelikan'),
(5, 'Bic'),
(6, 'Paper Mate'),
(7, 'Kiut'),
(10, 'Sharpie'),
(11, 'Estrella');

--
-- Dumping data for table `metodos`
--

INSERT INTO `metodos` (`id`, `nombre`, `tipo`, `cuenta`, `banco`, `tipoCi`, `ci`, `beneficiario`, `telefono`, `correo`) VALUES
(1, 'Pago Móvil Personal', 'Pago Móvil', NULL, 'BANCARIBE', 'Venezolano', '12312312', NULL, '04151231231', NULL),
(2, 'Pago Móvil Negocio', 'Pago Móvil', NULL, 'BANESCO', 'Jurídico', '4294102491204', NULL, '04151424346', NULL),
(3, 'Transferencia Negocio', 'Transferencia', '02440294912051920342', 'BANCO MERCANTIL', 'Jurídico', '4294102491204', 'Luis Pérez', NULL, NULL),
(4, 'Zelle Personal', 'Zelle', NULL, NULL, NULL, NULL, NULL, NULL, 'correozelle@gmail.com'),
(5, 'Binance Personal', 'Binance', NULL, NULL, NULL, NULL, NULL, NULL, 'correobinance@gmail.com');

--
-- Dumping data for table `pagos_choferes`
--

INSERT INTO `pagos_choferes` (`id`, `monto`, `idChofer`) VALUES
(1, 2.00, 1),
(2, 5.00, 4);

--
-- Dumping data for table `pagos_proveedores`
--

INSERT INTO `pagos_proveedores` (`id`, `fecha`, `monto`, `idProveedor`, `idUsuario`) VALUES
(1, '2025-03-05 00:00:00', 10000.00, 1, 1),
(2, '2025-03-05 00:00:00', 1000.62, 2, 1),
(3, '2025-03-05 00:00:00', 1000.00, 1, 1);

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `nombre`, `unidad`, `precioCompra`, `precioVenta`, `precioVenta2`, `precioVenta3`, `vendidoMayoreo`, `precioMayoreo`, `cantidadMayoreo`, `marca`, `categoria`, `proveedor`) VALUES
(1, '1321355155', 'Bocina', 'unid', 500.00, 1000.00, 900.00, 1100.00, 0, NULL, NULL, NULL, NULL, 1),
(2, '12123146556415', 'Thinner', 'lt', 15.00, 22.00, 0.00, 0.00, 0, NULL, NULL, 1, 1, 2),
(3, '113241564546', 'Cuaderno', 'unid', 15.00, 22.00, 20.00, 0.00, 1, 13.00, 10.00, 2, 1, 3),
(4, '1321354564', 'Lapiz', 'unid', 5.00, 10.00, 8.00, 15.00, 1, 8.00, 20.00, 3, 2, 4),
(5, '12131212545454', 'Mouse', 'unid', 50.00, 100.00, 0.00, 0.00, 0, NULL, NULL, NULL, NULL, 5),
(6, '11321345495', 'Teclado', 'unid', 105.00, 200.00, 250.00, 0.00, 0, NULL, NULL, NULL, NULL, 3),
(7, '1213221', 'Calculadora', 'unid', 25.00, 28.00, 20.00, 0.00, 0, NULL, NULL, NULL, NULL, 1),
(8, '123135135487487', 'Mecate', 'mt', 1.54, 4.00, 0.00, 0.00, 1, 3.00, 20.00, 4, NULL, 2);

--
-- Dumping data for table `productos_cotizados`
--

INSERT INTO `productos_cotizados` (`id`, `cantidad`, `precio`, `idProducto`, `idCotizacion`) VALUES
(1, 3.00, 22.00, 2, 1),
(2, 3.00, 10.00, 4, 1),
(3, 1.00, 1000.00, 1, 1),
(4, 3.00, 22.00, 3, 1),
(5, 100.00, 8.00, 4, 2),
(6, 4.00, 22.00, 3, 4),
(7, 3.00, 10.00, 4, 4),
(8, 3.00, 1000.00, 1, 4),
(9, 5.00, 200.00, 7, 4),
(10, 4.00, 100.00, 6, 4),
(11, 2.00, 10.00, 4, 5),
(12, 1.00, 200.00, 7, 5),
(13, 1.00, 100.00, 6, 5),
(14, 1.00, 1000.00, 1, 5),
(15, 1.00, 22.00, 3, 5);

--
-- Dumping data for table `productos_removidos`
--

INSERT INTO `productos_removidos` (`id`, `fecha`, `cantidad`, `idProducto`, `idUsuario`) VALUES
(1, '2025-01-02 12:00:00', 3.00, 2, 1);

--
-- Dumping data for table `productos_vendidos`
--

INSERT INTO `productos_vendidos` (`id`, `fecha`, `cantidad`, `precio`, `idProducto`, `idReferencia`, `tipo`) VALUES
(1, '2025-01-03 12:00:00', 1.00, 1000.00, 1, 1, 'cuenta'),
(2, '2025-01-03 12:00:00', 1.00, 20.00, 3, 1, 'venta'),
(3, '2025-01-02 12:00:00', 1.00, 22.00, 2, 1, 'venta'),
(4, '2025-01-03 12:00:00', 1.00, 1000.00, 1, 2, 'apartado'),
(5, '2025-01-02 12:00:00', 1.00, 22.00, 2, 2, 'venta'),
(6, '2025-01-02 12:00:00', 1.00, 10.00, 4, 2, 'venta'),
(7, '2025-01-03 12:00:00', 1.00, 1000.00, 1, 3, 'cuenta'),
(8, '2025-01-03 12:00:00', 1.00, 1000.00, 1, 4, 'apartado'),
(9, '2025-01-02 12:00:00', 1.00, 22.00, 2, 3, 'venta'),
(10, '2025-01-02 12:00:00', 1.00, 10.00, 4, 3, 'venta'),
(11, '2025-01-03 12:00:00', 1.00, 1000.00, 1, 4, 'venta'),
(12, '2025-01-03 12:00:00', 1.00, 22.00, 3, 5, 'cuenta'),
(13, '2025-01-03 12:00:00', 3.00, 10.00, 4, 5, 'venta'),
(14, '2025-01-03 12:00:00', 5.00, 22.00, 4, 1, 'cuenta');

--
-- Dumping data for table `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `telefono`, `rif`, `direccion`) VALUES
(1, 'Inversiones DAX', '04153940124', '304689714', 'Edificio 25, Maracay'),
(2, 'Soluciones ADV', '04157584900', '155920506', 'Calle 23, Local 10, Caracas'),
(3, 'Repuestos VA', '04150988907', '182912932', 'Calle 30, Local 15, Valencia'),
(4, 'Ferretería KLC', '04151234123', '314248981', 'Edificio 3, Local 2, Santa Cruz'),
(5, 'Fabricantes MPDM', '04153941234', '144893917', 'Calle 10, Local 4, Puerto Cabello');

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `permisos`) VALUES
(1, 'Administrador', '{\n  \"vistas.marcas_categorias\": true,\n  \"marcas.registrar\": true,\n  \"marcas.editar\": true,\n  \"marcas.eliminar\": true,\n  \"categorias.registrar\": true,\n  \"categorias.editar\": true,\n  \"categorias.eliminar\": true,\n  \"vistas.choferes\": true,\n  \"choferes.editar\": true,\n  \"choferes.pagar_chofer\": true,\n  \"vistas.deliveries\": true,\n  \"vistas.clientes\": true,\n  \"clientes.registrar\": true,\n  \"clientes.editar\": true,\n  \"clientes.eliminar\": true,\n  \"vistas.metodos\": true,\n  \"metodos.registrar\": true,\n  \"metodos.editar\": true,\n  \"metodos.eliminar\": true,\n  \"vistas.inventario\": true,\n  \"productos.registrar\": true,\n  \"productos.editar\": true,\n  \"productos.eliminar\": true,\n  \"productos.agregar_existencia\": true,\n  \"productos.remover_existencia\": true,\n  \"vistas.proveedores\": true,\n  \"proveedores.registrar\": true,\n  \"proveedores.editar\": true,\n  \"vistas.pagos\": true,\n  \"proveedores.pagar_proveedor\": true,\n  \"vistas.roles\": true,\n  \"roles.registrar\": true,\n  \"roles.editar\": true,\n  \"vistas.usuarios\": true,\n  \"usuarios.registrar\": true,\n  \"usuarios.editar\": true,\n  \"usuarios.eliminar\": true,\n  \"vistas.ventas\": true,\n  \"ventas.registrar_venta\": true,\n  \"vistas.cuentas\": true,\n  \"ventas.registrar_cuenta\": true,\n  \"vistas.apartados\": true,\n  \"ventas.registrar_apartado\": true,\n  \"vistas.cotizaciones\": true,\n  \"ventas.registrar_cotiza\": true,\n  \"ventas.eliminar_cotiza\": true,\n  \"vistas.abonos\": true,\n  \"ventas.realizar_abono\": true,\n  \"vistas.historial\": true\n}');

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `nombre`, `telefono`, `password`, `idRol`) VALUES
(1, 'Admin', 'Luis Pérez', '0415010123', '$2y$12$iiwCElOZFXDK4JjCg0BL2Oza.gTzIFfq0UL4RggVJw05psOA1Mhq2', 1);

--
-- Dumping data for table `ventas`
--

INSERT INTO `ventas` (`id`, `fecha`, `pagado`, `total`, `origen`, `simple`, `idMetodo`, `idCliente`, `idUsuario`) VALUES
(1, '2025-02-25 17:30:52', 54.00, 54.00, NULL, 'Punto de Venta', NULL, 1, 1),
(2, '2025-02-25 17:52:56', 51.50, 51.50, '04151231234', NULL, 2, 1, 1),
(3, '2025-02-28 13:38:54', 59.50, 59.50, NULL, 'Efectivo (Bs)', NULL, 2, 1),
(4, '2025-02-28 14:48:09', 1000.00, 1000.00, 'pagozelle@correo.com', NULL, 4, 1, 1),
(5, '2025-02-28 16:43:50', 30.00, 30.00, NULL, 'Punto de Venta', NULL, 3, 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
