import express from 'express'
import fileUpload from 'express-fileupload'
import { WhatsApp } from './socket.mjs'

const app = express()
const port = 3000

app.use(fileUpload())

app.post('/pdf', async (req, res) => {
  const numero = req.query.numero.slice(1)
  const archivo = req.files.pdf

  if (!req.files || Object.keys(req.files).length === 0 || !numero || !archivo) {
    return res.status(400).send('Falta archivo o numero de telefono.')
  }

  await WhatsApp.connect()

  await WhatsApp.file(numero, archivo.data)

  console.log('archivo enviado?')

  res.setHeader('Access-Control-Allow-Origin', 'http://localhost:8080')
  res.status(200).json({
    mensaje: 'Archivo enviado de forma exitosa.',
  })
})

app.listen(port, () => {
  console.log(`API de archivos escuchando en el puertos ${port}`)
})
