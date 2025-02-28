import { parentPort } from 'node:worker_threads'
import cron from 'node-cron'

process.env.TZ = 'America/Caracas'

const API_URL = 'http://localhost/api/ventas.php'
const TELEFONO_DUEÑO = '4128970019'
const TRES_VECES_AL_DIA = '0 8,13,18 * * *'

cron.schedule(TRES_VECES_AL_DIA, async () => {
  console.log('Revisando cuentas vencidas...')

  try {
    const respuesta = await fetch(API_URL, {
      method: 'POST',
      body: JSON.stringify({ accion: 'obtener_cuentas' })
    })

    const { cuentas } = await respuesta.json()
    cuentas.forEach(async cuenta => {
      const { dias, porPagar, productos, nombreCliente, telefonoCliente, notificado } = cuenta

      if (telefonoCliente === '') return

      const ONE_DAY_ON_MS = 1000 * 60 * 60 * 24

      const fecha = new Date(cuenta.fecha)
      fecha.setHours(0, 0, 0, 0)

      const limite = new Date(fecha.getTime() + (ONE_DAY_ON_MS * dias))
      const ahora = new Date()
      ahora.setHours(0, 0, 0, 0)

      const vencido = ahora.getTime() > limite.getTime()
      const pago = Number(porPagar) > 0

      if (!vencido || !pago || notificado) return

      const respuestaNotificado = await fetch(API_URL, {
        method: 'POST',
        body: JSON.stringify({
          accion: 'marcar_notificado',
          id: cuenta.id,
        })
      })

      const resultado = await respuestaNotificado.json()

      if (!resultado) {
        console.log('No se pudo marcar como notificado...', cuenta.id)
        return
      }

      const formato = fecha => new Intl.DateTimeFormat('es', {
        dateStyle: 'full',
      }).format(fecha)

      const mensaje = `*NOMBRE DE EMPRESA C.A.*\n\nEstimado/a *${nombreCliente}*, le notificamos que su cuenta pendiente desde el día *${formato(fecha)}* con una deuda de *$${porPagar}* ha caducado:\n    ${productos.map(producto => `- ${producto.nombre} (${producto.cantidad} ${producto.unidad}.)`).join('\n    ')}`

      const telefono = telefonoCliente.slice(1)

      parentPort.postMessage({ phone: telefono, text: mensaje })
      parentPort.postMessage({ phone: TELEFONO_DUEÑO, text: mensaje })

      console.log('Cuenta notificada: ', cuenta.id)
    })
  } catch (error) {
    console.error(error)
  }
})

console.log('La tarea programada de cuentas vencidas ha sido activada.')
