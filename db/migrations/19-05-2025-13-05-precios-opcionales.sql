# UP

ALTER TABLE productos MODIFY precioVenta2 DECIMAL(8,2) NULL DEFAULT 0;
ALTER TABLE productos MODIFY precioVenta3 DECIMAL(8,2) NULL DEFAULT 0;
ALTER TABLE productos MODIFY precioVenta4 DECIMAL(8,2) NULL DEFAULT 0;

# DOWN

ALTER TABLE productos MODIFY precioVenta2 DECIMAL(8,2) NOT NULL DEFAULT 0;
ALTER TABLE productos MODIFY precioVenta3 DECIMAL(8,2) NOT NULL DEFAULT 0;
ALTER TABLE productos MODIFY precioVenta4 DECIMAL(8,2) NOT NULL DEFAULT 0;
