# UP

ALTER TABLE pagos_proveedores ADD COLUMN factura VARCHAR(50) NOT NULL DEFAULT 'N/A' AFTER monto;

# DOWN

ALTER TABLE pagos_proveedores DROP COLUMN factura;
