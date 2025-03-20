import 'dotenv/config'
import express from 'express'
import fileUpload from 'express-fileupload' 
import bodyParser from 'body-parser'
import { logger } from './logger.mjs'
import { getReminderMessages } from './reminders.mjs'
import { connect } from './whatsapp.mjs'

const { NOTIFS_PORT, NOTIFS_HOST, NOTIFS_SCHEME, WEB_URL, OWNER_NAME } = process.env

const log = logger()
const app = express()

app.use(bodyParser.json())
app.use(fileUpload())
app.use((_, res, next) => {
  res.setHeader('Access-Control-Allow-Origin', WEB_URL)
  next()
})

app.post('/pdf', async (req, res) => {
  log.status('Recibiendo peticion de archivos...')
  const phone = req.query.numero.slice(1)
  const file = req.files.pdf

  if (!req.files || Object.keys(req.files).length === 0 || !file || !phone) {
    log.error('Error en la peticion, falta archivo o numero')
    return res.status(400).send({
      message: 'Falta archivo o numero de telefono.'
    })
  }

  const array = new Uint8Array(file.data)
  const buffer = Buffer.from(array)
  const name = OWNER_NAME + ' - Comprobante.pdf'

  try {
    const WhatsApp = await connect()
    await WhatsApp.file(phone, buffer, name)
  } catch (error) {
    log.error('Error durante el envio de comprobante')
    log.error(error)
  }

  return res.status(200).json({
    mensaje: 'Archivo enviado de forma exitosa.',
  })
})

app.post('/cuentas', async (req, res) => {
  const { cuentas } = req.body

  if (!cuentas || !Array.isArray(cuentas)) {
    log.error('Error en la peticion, falta campo de cuentas')
    return res.status(400).json({
      message: 'Falta campo de cuentas'
    })
  }

  let messages = getReminderMessages(cuentas)

  for (let index = 0; index < 2; index++) {
    messages = messages.concat(messages)
  }

  log.status(messages)

  if (messages.length === 0) {
    return res.status(200).json({
      message: 'No hay cuentas por notificar'
    })
  }

  try {
    const WhatsApp = await connect()
    messages.forEach(({ phone, text }) => WhatsApp.message(phone, text))
  } catch (error) {
    log.error('Error durante el envio de recordatorios')
    log.error(error)
  }

  return res.status(200).json({
    mensaje: 'Notificaciones enviadas de forma correcta.',
  })
})

app.listen(NOTIFS_PORT, NOTIFS_HOST, () => {
  log.status(`API de archivos escuchando en: ${NOTIFS_SCHEME}://${NOTIFS_HOST}:${NOTIFS_PORT}`)
})
