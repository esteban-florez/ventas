-- Migración: Agrega tablas y lógica para manejar pagos múltiples en ventas y abonos.

-- UP MIGRATION

START TRANSACTION;

-- 1. Crear nuevas tablas para manejar pagos múltiples en ventas y abonos

CREATE TABLE `pagos_ventas` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `idVenta` BIGINT UNSIGNED NOT NULL,
    `idMetodo` BIGINT UNSIGNED,
    `monto` DECIMAL(9,2) NOT NULL,
    `origen` VARCHAR(50),
    `simple` VARCHAR(20),
    `fecha` DATETIME NOT NULL,
    FOREIGN KEY (`idVenta`) REFERENCES `ventas`(`id`),
    FOREIGN KEY (`idMetodo`) REFERENCES `metodos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pagos_abonos` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `idAbono` BIGINT UNSIGNED NOT NULL,
    `idMetodo` BIGINT UNSIGNED,
    `monto` DECIMAL(9,2) NOT NULL,
    `origen` VARCHAR(50),
    `simple` VARCHAR(20),
    `fecha` DATETIME NOT NULL,
    FOREIGN KEY (`idAbono`) REFERENCES `abonos`(`id`),
    FOREIGN KEY (`idMetodo`) REFERENCES `metodos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Modificar tablas existentes para agregar una columna de control

ALTER TABLE `ventas` ADD COLUMN `usa_pagos_multiples` BOOLEAN DEFAULT FALSE;
ALTER TABLE `abonos` ADD COLUMN `usa_pagos_multiples` BOOLEAN DEFAULT FALSE;

-- 3. Migrar datos de pagos existentes de las columnas antiguas a las nuevas tablas

INSERT INTO `pagos_ventas` (`idVenta`, `idMetodo`, `monto`, `origen`, `simple`, `fecha`)
SELECT `id`, `idMetodo`, `pagado`, `origen`, `simple`, `fecha`
FROM `ventas`
WHERE `idMetodo` IS NOT NULL OR `origen` IS NOT NULL OR `simple` IS NOT NULL;

INSERT INTO `pagos_abonos` (`idAbono`, `idMetodo`, `monto`, `origen`, `simple`, `fecha`)
SELECT `id`, `idMetodo`, `monto`, `origen`, `simple`, `fecha`
FROM `abonos`
WHERE `idMetodo` IS NOT NULL OR `origen` IS NOT NULL OR `simple` IS NOT NULL;

-- Marcar las ventas y abonos que fueron migradas para usar el nuevo sistema
UPDATE `ventas`
SET `usa_pagos_multiples` = TRUE
WHERE `id` IN (SELECT `idVenta` FROM `pagos_ventas`);

UPDATE `abonos`
SET `usa_pagos_multiples` = TRUE
WHERE `id` IN (SELECT `idAbono` FROM `pagos_abonos`);

COMMIT;

-- DOWN MIGRATION

START TRANSACTION;

-- Desactivar temporalmente la verificación de claves foráneas para poder eliminar tablas
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Eliminar las nuevas tablas de pagos
DROP TABLE IF EXISTS `pagos_ventas`;
DROP TABLE IF EXISTS `pagos_abonos`;

-- 2. Eliminar la columna de control agregada a las tablas existentes
ALTER TABLE `ventas` DROP COLUMN IF EXISTS `usa_pagos_multiples`;
ALTER TABLE `abonos` DROP COLUMN IF EXISTS `usa_pagos_multiples`;

-- Reactivar la verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

COMMIT;
