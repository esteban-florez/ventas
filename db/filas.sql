-- /*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.6.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: ventas
-- ------------------------------------------------------
-- Server version	11.6.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;


--
-- Dumping data for table `abonos`
--

LOCK TABLES `abonos` WRITE;
/*!40000 ALTER TABLE `abonos` DISABLE KEYS */;
INSERT INTO `abonos` VALUES
(1,'2025-02-28 14:48:09',102.00,'04151231234',NULL,1,1),
(2,'2025-02-28 14:48:09',1000.00,'02001234090012348080',NULL,3,2),
(3,'2025-02-28 14:48:09',900.00,'zelle123@ejemplo.com',NULL,4,3),
(4,'2025-02-28 14:48:09',500.00,'binance123@ejemplo.com',NULL,5,4),
(5,'2025-02-28 14:48:09',22.00,NULL,'Punto de Venta',NULL,5);
/*!40000 ALTER TABLE `abonos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES
(1,'LIBRETAS'),
(2,'LAPICES'),
(3,'COLORES DE MADERA'),
(4,'PLUMONES'),
(5,'CRAYOLAS'),
(8,'ARTICULOS DE OFICINA');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `choferes`
--

LOCK TABLES `choferes` WRITE;
/*!40000 ALTER TABLE `choferes` DISABLE KEYS */;
INSERT INTO `choferes` VALUES
(1,'Adrían Díaz','04153940124','Venezolano','22147159'),
(2,'María Pérez','04157584900','Venezolano','15592050'),
(3,'Juan González','04150988907','Venezolano','18291293'),
(4,'Luisa Medina','04151234123','Venezolano','31424898'),
(5,'Pedro Vargas','04153941234','Venezolano','14489391');
/*!40000 ALTER TABLE `choferes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES
(1,'paco rivera bernabe','04128970019','Venezolano','24145159'),
(2,'David Gonzalez','04128970019','Venezolano','17592050'),
(3,'ejemplo de cliente','','Venezolano','8241293'),
(4,'ejemplo de otro cliente','04128970019','Venezolano','19424098'),
(5,'otrooooo clienteee','','Venezolano','10482391');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `configuracion`
--

LOCK TABLES `configuracion` WRITE;
/*!40000 ALTER TABLE `configuracion` DISABLE KEYS */;
INSERT INTO `configuracion` VALUES
('Super Mercado','777666123','./logos/logo.png');
/*!40000 ALTER TABLE `configuracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cotizaciones`
--

LOCK TABLES `cotizaciones` WRITE;
/*!40000 ALTER TABLE `cotizaciones` DISABLE KEYS */;
INSERT INTO `cotizaciones` VALUES
(1,'2022-07-03 16:33:23',1162.00,'2022-07-13',2,1),
(2,'2022-07-03 16:36:43',1000.00,'2022-07-09',1,1),
(3,'2022-07-06 14:33:04',4518.00,'2022-07-28',2,1),
(4,'2022-07-06 18:10:45',1342.00,'2022-07-15',2,1),
(5,'2022-08-01 09:54:23',6594.00,'2022-08-15',4,1);
/*!40000 ALTER TABLE `cotizaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cuentas_apartados`
--

LOCK TABLES `cuentas_apartados` WRITE;
/*!40000 ALTER TABLE `cuentas_apartados` DISABLE KEYS */;
INSERT INTO `cuentas_apartados` VALUES
(1,'2025-02-18 17:30:03',1002.00,7,'cuenta',2,1),
(2,'2025-02-19 17:36:09',1000.00,NULL,'apartado',1,1),
(3,'2025-02-19 17:53:10',1010.50,10,'cuenta',1,1),
(4,'2025-02-19 17:53:25',1000.00,NULL,'apartado',2,1),
(5,'2025-02-19 17:53:15',22.00,15,'cuenta',1,1);
/*!40000 ALTER TABLE `cuentas_apartados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
INSERT INTO `deliveries` VALUES
(1,4.0,'Calle 25, Casa 10, Barrio Ejemplo, Valencia, Carabobo',1,1,1,NULL),
(2,2.0,'Calle 50, Casa 42, Barrio Ejemplo, Maracay, Aragua',1,2,NULL,1),
(3,1.5,'Calle 61, Casa 84, Barrio Ejemplo, Caracas, Dtto Capital',0,3,2,NULL),
(4,10.5,'Calle 22, Casa 63, Barrio Ejemplo, Maracaibo, Zulia',0,4,NULL,3),
(5,9.5,'Calle 40, Casa 37, Barrio Ejemplo, Cagua, Aragua',1,5,3,NULL);
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `entradas` WRITE;
/*!40000 ALTER TABLE `entradas` DISABLE KEYS */;
INSERT INTO `entradas` VALUES
(1,'2025-01-02 11:00:00',25,1),
(2,'2025-01-01 11:00:00',99,2),
(3,'2025-01-03 11:00:00',66,3),
(4,'2025-01-01 11:00:00',453,4),
(5,'2025-01-01 11:00:00',96,5),
(6,'2025-01-03 11:00:00',496,6),
(7,'2025-01-01 11:00:00',11,7),
(8,'2025-01-02 11:00:00',3,8);
/*!40000 ALTER TABLE `entradas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `marcas`
--

LOCK TABLES `marcas` WRITE;
/*!40000 ALTER TABLE `marcas` DISABLE KEYS */;
INSERT INTO `marcas` VALUES
(1,'NORMA'),
(2,'SCRIBE'),
(3,'MAPED'),
(4,'PELIKAN'),
(5,'BIC'),
(6,'PAPER MATE'),
(7,'KIUT'),
(10,'SHARPIE'),
(11,'ESTRELLA');
/*!40000 ALTER TABLE `marcas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `metodos`
--

LOCK TABLES `metodos` WRITE;
/*!40000 ALTER TABLE `metodos` DISABLE KEYS */;
INSERT INTO `metodos` VALUES
(1,'Pago Móvil Personal','Pago Móvil',NULL,'BANCARIBE','Venezolano','12312312',NULL,'04151231231',NULL),
(2,'Pago Móvil Negocio','Pago Móvil',NULL,'BANESCO','Jurídico','4294102491204',NULL,'04151424346',NULL),
(3,'Transferencia Negocio','Transferencia','02440294912051920342','BANCO MERCANTIL','Jurídico','4294102491204','Luis Pérez',NULL,NULL),
(4,'Zelle Personal','Zelle',NULL,NULL,NULL,NULL,NULL,NULL,'correozelle@gmail.com'),
(5,'Binance Personal','Binance',NULL,NULL,NULL,NULL,NULL,NULL,'correobinance@gmail.com');
/*!40000 ALTER TABLE `metodos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES
(1,'1321355155','Bocina','unid',500.00,1000.00,900.00,1100.00,0,NULL,NULL,NULL,NULL,1),
(2,'12123146556415','Thinner','lt',15.00,22.00,NULL,NULL,0,NULL,NULL,1,1,2),
(3,'113241564546','Cuadernocosido','unid',15.00,22.00,20.00,NULL,1,13.00,10,2,1,3),
(4,'1321354564','Lapizbonito','unid',5.00,10.00,8.00,15.00,1,8.00,20,3,2,4),
(5,'12131212545454','Mouseinalámbrico','unid',50.00,100.00,NULL,NULL,0,NULL,NULL,NULL,NULL,5),
(6,'11321345495','Tecladoinálambrico','unid',105.00,200.00,250.00,NULL,0,NULL,NULL,NULL,NULL,3),
(7,'1213221','Calculadora','unid',25.00,28.00,20.00,NULL,0,NULL,NULL,NULL,NULL,1),
(8,'123135135487487','Mecate','mt',1.54,4.00,NULL,NULL,1,3.00,20,4,NULL,2);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `productos_vendidos`
--

LOCK TABLES `productos_vendidos` WRITE;
/*!40000 ALTER TABLE `productos_vendidos` DISABLE KEYS */;
INSERT INTO `productos_vendidos` VALUES
(1,'2025-01-03 12:00:00',1.00,1000.00,1,1,'cuenta'),
(2,'2025-01-03 12:00:00',1.00,20.00,3,1,'venta'),
(3,'2025-01-02 12:00:00',1.00,22.00,2,1,'venta'),
(4,'2025-01-03 12:00:00',1.00,1000.00,1,2,'apartado'),
(5,'2025-01-02 12:00:00',1.00,22.00,2,2,'venta'),
(6,'2025-01-02 12:00:00',1.00,10.00,4,2,'venta'),
(7,'2025-01-03 12:00:00',1.00,1000.00,1,3,'cuenta'),
(8,'2025-01-03 12:00:00',1.00,1000.00,1,4,'apartado'),
(9,'2025-01-02 12:00:00',1.00,22.00,2,3,'venta'),
(10,'2025-01-02 12:00:00',1.00,10.00,4,3,'venta'),
(11,'2025-01-03 12:00:00',1.00,1000.00,1,4,'venta'),
(12,'2025-01-03 12:00:00',1.00,22.00,3,5,'cuenta'),
(13,'2025-01-03 12:00:00',3.00,10.00,4,5,'venta'),
(14,'2025-01-03 12:00:00',5.00,22.00,4,1,'cuenta');
/*!40000 ALTER TABLE `productos_vendidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `productos_cotizados`
--

LOCK TABLES `productos_cotizados` WRITE;
/*!40000 ALTER TABLE `productos_cotizados` DISABLE KEYS */;
INSERT INTO `productos_cotizados` VALUES
(1,3.00,22.00,2,1),
(2,3.00,10.00,4,1),
(3,1.00,1000.00,1,1),
(4,3.00,22.00,3,1),
(5,100.00,8.00,4,2),
(6,4.00,22.00,3,4),
(7,3.00,10.00,4,4),
(8,3.00,1000.00,1,4),
(9,5.00,200.00,7,4),
(10,4.00,100.00,6,4),
(11,2.00,10.00,4,5),
(12,1.00,200.00,7,5),
(13,1.00,100.00,6,5),
(14,1.00,1000.00,1,5),
(15,1.00,22.00,3,5);
/*!40000 ALTER TABLE `productos_cotizados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES
(1,'Inversiones DAX','04153940124','304689714','Edificio 25, Maracay'),
(2,'Soluciones ADV','04157584900','155920506','Calle 23, Local 10, Caracas'),
(3,'Repuestos VA','04150988907','182912932','Calle 30, Local 15, Valencia'),
(4,'Ferretería KLC','04151234123','314248981','Edificio 3, Local 2, Santa Cruz'),
(5,'Fabricantes MPDM','04153941234','144893917','Calle 10, Local 4, Puerto Cabello');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(1,'Admin','Luis Pérez','23112314523125','$2y$12$iiwCElOZFXDK4JjCg0BL2Oza.gTzIFfq0UL4RggVJw05psOA1Mhq2');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES
(1,'2025-02-25 17:30:52',54.00,54.00,NULL,'Punto de Venta',NULL,1,1),
(2,'2025-02-25 17:52:56',51.50,51.50,'04151231234',NULL,2,1,1),
(3,'2025-02-28 13:38:54',59.50,59.50,NULL,'Efectivo (Bs)',NULL,2,1),
(4,'2025-02-28 14:48:09',1000.00,1000.00,'pagozelle@correo.com',NULL,4,1,1),
(5,'2025-02-28 16:43:50',30.00,30.00,NULL,'Punto de Venta',NULL,3,1);
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-02-05  9:08:38
