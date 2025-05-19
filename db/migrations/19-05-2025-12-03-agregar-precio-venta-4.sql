# UP

ALTER TABLE productos ADD COLUMN precioVenta4 DECIMAL(8,2) NOT NULL DEFAULT 0 AFTER precioVenta3;

# DOWN

ALTER TABLE productos DROP COLUMN precioVenta4;
