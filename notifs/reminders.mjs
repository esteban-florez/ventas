import { logger } from './logger.mjs'

process.env.TZ = 'America/Caracas'

const log = logger()
const { OWNER_NAME } = process.env

export function getReminderMessages(cuentas) {
  log.status('Verificando cuentas vencidas...')

  const messages = []
  for (const cuenta of cuentas) {
    const { dias, porPagar, telefonoCliente, fecha } = cuenta
    if (telefonoCliente === '') continue
  
    const date = new Date(fecha)
    const outdated = isOutdated(date, dias)
    const debt = Number(porPagar) > 0
  
    if (!outdated || !debt) continue
    
    log.status(`Cuenta por notificar: ${cuenta.id}`)
    const message = formatMessage(cuenta, date)
    messages.push({ phone: telefonoCliente, text: message })
  }

  return messages
}

function isOutdated(date, days) {
  const ONE_DAY_ON_MS = 1000 * 60 * 60 * 24
  const copy = new Date(date)
  copy.setHours(0, 0, 0, 0)

  const daysOnMs = ONE_DAY_ON_MS * days
  const limit = new Date(copy.getTime() + daysOnMs)
  const now = new Date()
  now.setHours(0, 0, 0, 0)

  return now.getTime() > limit.getTime()
}

function localeDate(date) {
  return new Intl.DateTimeFormat('es', {
    dateStyle: 'full',
  }).format(date)
}

function formatMessage(cuenta, date) {
  const { productos, nombreCliente, porPagar } = cuenta

  const productList = productos
    .map(producto => `- ${producto.nombre} (${producto.cantidad} ${producto.unidad}.)`)
    .join('\n    ')

  return `*${OWNER_NAME}*\n\nEstimado/a *${nombreCliente}*, le notificamos que su cuenta pendiente desde el día *${localeDate(date)}* con una deuda de *$${porPagar}* ha caducado:\n    ${productList}`
}
