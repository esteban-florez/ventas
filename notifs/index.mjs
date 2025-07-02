import "dotenv/config"
import express from "express"
import bodyParser from "body-parser"
import { logger } from "./logger.mjs"
import { getReminderMessages } from "./reminders.mjs"
import { getNewProductMessage } from "./new-product.mjs"
import { connect } from "./whatsapp.mjs"

const {
  NOTIFS_PORT,
  NOTIFS_HOST,
  NOTIFS_URL,
  NOTIFS_API_KEY,
  WEB_URL,
  OWNER_NAME,
  OWNER_PHONE,
  WHATSAPP_PHONE,
} = process.env

const log = logger()
const app = express()

app.use(bodyParser.json({ limit: "50mb" }))

app.use((req, res, next) => {
  const apiKey = req.headers["x-api-key"]

  if (!apiKey || apiKey !== NOTIFS_API_KEY) {
    return res.status(401).json({
      message: "Clave de API no válida",
    })
  }

  res.setHeader("Access-Control-Allow-Origin", WEB_URL)
  next()
})

app.post("/ws-api/pdf", async (req, res) => {
  log.status("Recibiendo peticion de archivos...")
  const phone = req.query.numero
  const base64 = req.body.pdf

  if (!base64 || !phone) {
    log.error("Error en la peticion, falta archivo o numero")
    return res.status(400).json({
      message: "Falta archivo o numero de telefono.",
    })
  }

  const buffer = Buffer.from(base64, "base64")
  const name = OWNER_NAME + " - Comprobante.pdf"

  try {
    const WhatsApp = await connect(WHATSAPP_PHONE)
    await WhatsApp.file(phone, buffer, name)
  } catch (error) {
    log.error("Error durante el envio de comprobante")
    log.error(error)
    return res.status(500).json({
      mensaje: "Hubo un error al enviar el comprobante",
    })
  }

  return res.status(200).json({
    mensaje: "Archivo enviado de forma exitosa.",
  })
})

app.post("/ws-api/cuentas", async (req, res) => {
  const { cuentas } = req.body

  if (!cuentas || !Array.isArray(cuentas)) {
    log.error("Error en la peticion, falta campo de cuentas")
    return res.status(400).json({
      message: "Falta campo de cuentas",
    })
  }

  const messages = getReminderMessages(cuentas)
  const forwarded = messages.map(({ text }) => ({ phone: OWNER_PHONE, text }))
  const allMessages = messages.concat(forwarded)

  if (messages.length === 0) {
    return res.status(200).json({
      message: "No hay cuentas por notificar",
    })
  }

  res.status(200).json({
    mensaje: "Notificaciones recibidas, empezando proceso de envio.",
  })

  try {
    const WhatsApp = await connect(WHATSAPP_PHONE)
    for (const { phone, text } of allMessages) {
      try {
        await WhatsApp.message(phone, text)
        await delay(2000)
      } catch (error) {
        log.error(`Error enviando mensaje a ${phone}: ${error}`)
        // TODO -> Implementar lógica de reintento aquí
      }
    }
    log.status("Envio de recordatorios finalizado")
  } catch (error) {
    log.error("Error durante el envio de recordatorios")
    log.error(error)
  }
})

app.post("/ws-api/producto", async (req, res) => {
  const { producto, clientes } = req.body

  if (!producto || !clientes || !Array.isArray(clientes)) {
    log.error("Error en la peticion, falta campo de producto o clientes")
    return res.status(400).json({
      message: "Falta campo de producto o clientes",
    })
  }

  res.status(200).json({
    mensaje: "Producto y clientes recibidos, empezando proceso de envio.",
  })

  const message = getNewProductMessage(producto)

  try {
    const WhatsApp = await connect(WHATSAPP_PHONE)
    for (const { telefono } of clientes) {
      try {
        await WhatsApp.message(telefono, message)
        await delay(2000)
      } catch (error) {
        log.error(
          `Error enviando alerta de nuevo producto a ${telefono}: ${error}`
        )
        // TODO -> Implementar lógica de reintento aquí
      }
    }
    log.status("Envio de alertas de nuevo producto finalizado")
  } catch (error) {
    log.error("Error durante envio de alertas de nuevo producto recordatorios")
    log.error(error)
  }
})

app.listen(NOTIFS_PORT, NOTIFS_HOST, () => {
  log.status(`API de notificaciones escuchando en: ${NOTIFS_URL}`)
})

function delay(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms))
}
