import { parentPort } from 'node:worker_threads'
import cron from 'node-cron'
import { logger } from './logger.mjs'
import fetch from 'node-fetch'

const log = logger()

process.env.TZ = 'America/Caracas'

const { API_URL, OWNER_PHONE, OWNER_NAME } = process.env
const THREE_TIMES_A_DAY = '* 9,13,18 * * *'
const EVERY_MINUTE = '* * * * *'

const endpoint = `${API_URL}/ventas.php`
const schedule = process.argv[4] === '--dev' ? EVERY_MINUTE : THREE_TIMES_A_DAY

cron.schedule(schedule, async () => {
  log.status('Ejecutando cron de cuentas vencidas...')

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      body: JSON.stringify({ accion: 'obtener_cuentas' })
    })

    const { cuentas } = await response.json()
    cuentas.forEach(checkOutdatedDebt)
  } catch (error) {
    log.error('Error al obtener cuentas de la API')
    log.error(error)
  }
})

log.status('La tarea programada de cuentas vencidas ha sido activada.')

async function checkOutdatedDebt(cuenta) {
  const { dias, porPagar, telefonoCliente, notificado, fecha } = cuenta

  if (telefonoCliente === '') return

  const date = new Date(fecha)
  const outdated = isOutdated(date, dias)
  const debt = Number(porPagar) > 0

  if (!outdated || !debt || notificado) return

  const phone = telefonoCliente.slice(1)
  const message = formatMessage(cuenta, date)

  parentPort.postMessage({ phone, text: message })
  parentPort.postMessage({ phone: OWNER_PHONE, text: message })

  await markAsNotified(cuenta.id)
  log.status(`Cuenta notificada: ${cuenta.id}`)
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

async function markAsNotified(id) {
  try {
    const responseNotified = await fetch(endpoint, {
      method: 'POST',
      body: JSON.stringify({
        accion: 'marcar_notificado',
        id,
      })
    })

    const result = await responseNotified.json()

    if (!result) {
      log.status(`No se pudo marcar como notificado... cliente sin telefono: ${id}`)
      return
    }
  
  } catch (error) {
    log.error(`Error al marcar cuenta como notificada ${cuenta.id}`)
    log.error(error)
  }
}
