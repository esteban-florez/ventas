import { parentPort } from 'node:worker_threads'
import express from 'express'
import fileUpload from 'express-fileupload'

const app = express()
const port = 3000

app.use(fileUpload())

app.post('/pdf', async (req, res) => {
  const numero = req.query.numero.slice(1)
  const archivo = req.files.pdf

  if (!req.files || Object.keys(req.files).length === 0 || !numero || !archivo) {
    return res.status(400).send('Falta archivo o numero de telefono.')
  }

  try {
    const array = new Uint8Array(archivo.data)
    parentPort.postMessage({ phone: numero, file: array })
    console.log('Reporte PDF enviado...')
  } catch (error) {
    console.error(error.message)
  }

  res.setHeader('Access-Control-Allow-Origin', 'http://localhost:8080')
  res.status(200).json({
    mensaje: 'Archivo enviado de forma exitosa.',
  })
})

app.listen(port, () => {
  console.log(`API de archivos escuchando en el puertos ${port}`)
})
