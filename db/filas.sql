/*M!999999\- enable the sandbox mode */ 
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
(1,'Adrían Díaz','04153940124', 'Venezolano', '22147159'),
(2,'María Pérez','04157584900', 'Venezolano', '15592050'),
(3,'Juan González', '04150988907', 'Venezolano', '18291293'),
(4,'Luisa Medina','04151234123', 'Venezolano', '31424898'),
(5,'Pedro Vargas','04153941234', 'Venezolano', '14489391');
/*!40000 ALTER TABLE `choferes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES
(1,'paco rivera bernabe','2311459874', 'Venezolano', '24145159'),
(2,'canela perez','2311469874', 'Venezolano', '17592050'),
(3,'ejemplo de cliente', '', 'Venezolano', '8241293'),
(4,'ejemplo de otro cliente','231213456987', 'Venezolano', '19424098'),
(5,'otrooooo clienteee','', 'Venezolano', '10482391'),
(7,'Lars Montriel','231212315121', 'Venezolano', '15920274');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `configuracion`
--

LOCK TABLES `configuracion` WRITE;
/*!40000 ALTER TABLE `configuracion` DISABLE KEYS */;
INSERT INTO `configuracion` VALUES
('Tienda de conveniencia Spiderman','777666123','./logos/logo.png');
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
(4,'2022-07-06 14:33:04',4518.00,'2022-07-28',2,1),
(5,'2022-07-06 18:10:45',1342.00,'2022-07-15',2,1),
(6,'2022-08-01 09:54:23',6594.00,'2022-08-15',4,1);
/*!40000 ALTER TABLE `cotizaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cuentas_apartados`
--

LOCK TABLES `cuentas_apartados` WRITE;
/*!40000 ALTER TABLE `cuentas_apartados` DISABLE KEYS */;
INSERT INTO `cuentas_apartados` VALUES
(1,'2022-06-25 17:30:03',1002.00,1000.00,0.00,7,'cuenta',2,1),
(2,'2022-06-25 17:36:09',1000.00,1000.00,0.00,NULL,'apartado',1,1),
(3,'2022-06-25 17:53:10',1000.00,910.50,100.00,10,'cuenta',1,1),
(4,'2022-06-25 17:53:25',1000.00,1000.00,0.00,NULL,'apartado',2,1),
(5,'2022-07-03 17:53:15',22.00,22.00,0.00,15,'cuenta',1,1),
(6,'2022-07-03 18:13:16',1000.00,300.00,700.00,NULL,'apartado',4,1),
(7,'2022-07-06 18:09:30',300.00,50.00,250.00,7,'cuenta',2,1),
(8,'2022-07-06 18:10:14',1022.00,0.00,1022.00,NULL,'apartado',4,1);
/*!40000 ALTER TABLE `cuentas_apartados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
INSERT INTO `deliveries` VALUES
(1,4.0,'Calle 25, Casa 10, Barrio Ejemplo, Valencia, Carabobo', 1, 1, 1, NULL),
(2,2.0,'Calle 50, Casa 42, Barrio Ejemplo, Maracay, Aragua', 1, 2, NULL, 1),
(3,1.5,'Calle 61, Casa 84, Barrio Ejemplo, Caracas, Dtto Capital', 0, 1, 2, NULL),
(4,10.5,'Calle 22, Casa 63, Barrio Ejemplo, Maracaibo, Zulia', 0, 3, NULL, 3),
(5,9.5,'Calle 40, Casa 37, Barrio Ejemplo, Cagua, Aragua', 1, 5, 3, NULL);
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
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
(1,'1321355155','Bocina', 'uds', 500.00,1000.00,900.00,1100.00,25,0,NULL,NULL,NULL,NULL),
(2,'12123146556415','Thinner', 'lt', 15.00,22.00,NULL,NULL,99,0,NULL,NULL,1,1),
(3,'113241564546','Cuaderno cosido ', 'uds', 15.00,22.00,20.00,NULL,66,1,13.00,10,2,1),
(4,'1321354564','Lapiz bonito', 'uds', 5.00,10.00,8.00,15.00,453,1,8.00,20,3,2),
(6,'12131212545454','Mouse inalámbrico ', 'uds', 50.00,100.00,NULL,NULL,96,0,NULL,NULL,NULL,NULL),
(7,'11321345495','Teclado inálambrico', 'uds', 105.00,200.00,250.00,NULL,496,0,NULL,NULL,NULL, NULL),
(8,'1213221','Calculadora', 'uds', 25.00,28.00,20.00,NULL,11,0,NULL,NULL,NULL,NULL),
(9,'123135135487487','Mecate', 'mt', 1.54,4.00,NULL,NULL,3,1,3.00,20,4,NULL);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `productos_vendidos`
--

LOCK TABLES `productos_vendidos` WRITE;
/*!40000 ALTER TABLE `productos_vendidos` DISABLE KEYS */;
INSERT INTO `productos_vendidos` VALUES
(1,1.00,1000.00,1,1,'cuenta'),
(2,1.00,20.00,3,1,'venta'),
(3,1.00,22.00,2,1,'venta'),
(4,1.00,1000.00,1,2,'apartado'),
(5,1.00,22.00,2,2,'venta'),
(6,1.00,10.00,4,2,'venta'),
(7,1.00,1000.00,1,3,'cuenta'),
(8,1.00,1000.00,1,4,'apartado'),
(9,1.00,22.00,2,3,'venta'),
(10,1.00,10.00,4,3,'venta'),
(11,1.00,1000.00,1,4,'venta'),
(12,1.00,22.00,3,5,'venta'),
(13,5.00,22.00,2,6,'venta'),
(14,15.00,13.00,3,7,'venta'),
(15,1.00,22.00,2,8,'venta'),
(16,1.00,22.00,3,9,'venta'),
(17,3.00,22.00,2,1,'cotiza'),
(18,3.00,10.00,4,1,'cotiza'),
(19,1.00,1000.00,1,1,'cotiza'),
(20,3.00,22.00,3,1,'cotiza'),
(21,100.00,8.00,4,2,'cotiza'),
(22,1.00,22.00,3,5,'cuenta'),
(23,1.00,1000.00,1,6,'apartado'),
(29,4.00,22.00,3,4,'cotiza'),
(30,3.00,10.00,4,4,'cotiza'),
(31,3.00,1000.00,1,4,'cotiza'),
(32,5.00,200.00,7,4,'cotiza'),
(33,4.00,100.00,6,4,'cotiza'),
(34,10.00,22.00,3,10,'venta'),
(35,2.00,10.00,4,10,'venta'),
(36,2.00,100.00,6,10,'venta'),
(37,1.00,200.00,7,10,'venta'),
(38,2.00,22.00,3,11,'venta'),
(39,2.00,10.00,4,11,'venta'),
(40,1.00,100.00,6,12,'venta'),
(41,1.00,200.00,7,12,'venta'),
(42,1.00,22.00,2,12,'venta'),
(43,1.00,10.00,4,12,'venta'),
(44,1.00,200.00,7,13,'venta'),
(45,10.00,10.00,4,14,'venta'),
(46,1.00,100.00,6,7,'cuenta'),
(47,1.00,200.00,7,7,'cuenta'),
(48,1.00,1000.00,1,8,'apartado'),
(49,1.00,22.00,2,8,'apartado'),
(50,2.00,10.00,4,5,'cotiza'),
(51,1.00,200.00,7,5,'cotiza'),
(52,1.00,100.00,6,5,'cotiza'),
(53,1.00,1000.00,1,5,'cotiza'),
(54,1.00,22.00,3,5,'cotiza'),
(55,3.00,22.00,3,15,'venta'),
(56,2.00,10.00,4,16,'venta'),
(57,1.00,22.00,3,16,'venta'),
(58,8.00,4.00,9,17,'venta'),
(59,10.00,10.00,4,17,'venta'),
(60,7.00,22.00,3,6,'cotiza'),
(61,6.00,1000.00,1,6,'cotiza'),
(62,20.00,22.00,2,6,'cotiza'),
(63,1.00,1000.00,1,7,'cotiza'),
(64,1.00,22.00,2,8,'cotiza'),
(65,1.00,22.00,2,9,'cotiza'),
(66,1.00,1000.00,1,10,'cotiza');
/*!40000 ALTER TABLE `productos_vendidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(1,'paco_hunter_dev','Francisco Rivera Hills','2311459874','$2y$10$gzUnjFNk5SUh3yGh2OXYeuIDtkzqUCGnJUnThqXjWoVU0/C9QCJb.'),
(2,'janiss','Janis Joplin Perez','6554789512','$2y$10$foxks8Dg7NMu95HiTUbdhOeA4sqTPzdLTaja3ktHNYlizPq34AlwK'),
(3,'robert_espy','Robert Rodriguez','231456984','$2y$10$d8v/L9j9bnWZTd5FkL8HxeNI0159F1inhGlJSMApHZoCwLuiYFomG'),
(5,'popeye','Popeye el marino','23112314523125','$2y$10$PFR9QYZ8viczcxU6s87IjuNKu70IdEEuIjGI3H1Ar9qYU7Ix2aSTi');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES
(1,'2025-02-25 17:30:52',42.00,54.00,NULL,'Punto de Venta',NULL,0,1),
(2,'2025-02-25 17:52:56',32.00,51.50,'04151231234',NULL,2,0,1),
(3,'2025-02-28 13:38:54',32.00,59.50,NULL,'Efectivo (Bs)',NULL,0,1),
(4,'2025-02-28 14:48:09',1000.00,1000.00,'pagozelle@correo.com',NULL,4,1,1),
(5,'2025-03-01 16:43:50',22.00,30.00,NULL,'Punto de Venta',NULL,0,1),
(6,'2025-03-01 17:25:46',110.00,200.00,NULL,'Efectivo ($)',NULL,0,1),
(7,'2025-03-02 18:05:34',195.00,200.00,'pagobinance@correo.com',NULL,5,0,1),
(8,'2025-03-02 18:15:48',22.00,50.00,'04151065234',NULL,1,0,1),
(9,'2025-03-02 18:18:22',22.00,50.00,'04151231234',NULL,2,0,1),
(10,'2025-03-06 14:57:27',640.00,1000.00,NULL,'Punto de Venta',NULL,0,1),
(11,'2025-03-06 17:56:18',64.00,100.00,'04151231524',NULL,1,0,1),
(12,'2025-03-06 17:59:02',332.00,332.00,'02201234432190990231',NULL,3,0,1),
(13,'2025-03-06 17:59:27',200.00,200.00,'02201234432190990231',NULL,3,2,2),
(14,'2025-03-06 18:09:03',100.00,100.00,'04151231234',NULL,2,0,2),
(15,'2025-03-15 13:20:43',66.00,100.00,NULL,'Punto de Venta',NULL,0,1),
(16,'2025-03-15 16:41:45',42.00,50.00,NULL,'Efectivo (Bs)',NULL,4,1),
(17,'2025-03-15 16:43:23',132.00,500.00,NULL,'Efectivo ($)',NULL,0,5);
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
