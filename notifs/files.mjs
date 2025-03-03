import { parentPort } from 'node:worker_threads'
import express from 'express'
import fileUpload from 'express-fileupload'
import { logger } from './logger.mjs'

const log = logger()

const app = express()
const { FILES_PORT, FILES_HOST, WEB_URL } = process.env

app.use(fileUpload())

app.post('/pdf', async (req, res) => {
  log.status('Recibiendo peticion de archivos...')
  const phone = req.query.numero.slice(1)
  const file = req.files.pdf

  if (!req.files || Object.keys(req.files).length === 0 || !file || !phone) {
    log.error('Error en la peticion, falta archivo o numero')
    return res.status(400).send('Falta archivo o numero de telefono.')
  }

  const array = new Uint8Array(file.data)
  parentPort.postMessage({ phone, file: array })

  res.setHeader('Access-Control-Allow-Origin', WEB_URL)
  res.status(200).json({
    mensaje: 'Archivo enviado de forma exitosa.',
  })
})

app.listen(FILES_PORT, FILES_HOST, () => {
  log.status(`API de archivos escuchando en: http://${FILES_HOST}:${FILES_PORT}`)
})
