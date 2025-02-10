export const TIPOS_CLIENTE = ['Venezolano', 'Extranjero', 'Jurídico']

export const UNIDADES = {
  uds: 'Unidades',
  cm: 'Centímetros',
  mt: 'Metros',
  km: 'Kilómetros',
  g: 'Gramos',
  mg: 'Miligramos',
  kg: 'Kilogramos',
  ton: 'Toneladas',
  ml: 'Mililitros',
  lt: 'Litros',
}

export const DIAS = [7, 10, 15]

export const TIPOS_PAGO_SIMPLE = {
  punto: 'Punto de Venta',
  dolar: 'Efectivo ($)',
  bolivar: 'Efectivo (Bs)',
}

export const TIPOS_PAGO_CRUD = {
  pagomovil: 'Pago Móvil',
  transferencia: 'Transferencia',
  zelle: 'Zelle',
  binance: 'Binance',
}

export const TIPOS_PAGO = {
  ...TIPOS_PAGO_SIMPLE,
  ...TIPOS_PAGO_CRUD,
}

export const BANCOS = [
  'BANCAMIGA',
  'BANCO ACTIVO',
  'BANCO AGRICOLA',
  'BANCO CARONI',
  'BANCO DE VENEZUELA',
  'BANCO DEL CARIBE',
  'BANCO DEL SUR',
  'BANCO DEL TESORO',
  'BANCO DIGITAL DE LOS TRABAJDORES',
  'BANCO EXTERIOR',
  'BANCO MERCANTIL',
  'BANCO PLAZA',
  'BANCO PROVINCIAL',
  'BANCRECER',
  'BANESCO',
  'BANFANB',
  'BANGENTE',
  'BANPLUS',
  'BFC',
  'MI BANCO',
  'NACIONAL DE CREDITO',
  'SOFITASA',
  'VENEZOLANO DE CREDITO',
  '100 X 100 BANCO',
]
