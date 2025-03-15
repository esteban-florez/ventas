import 'dotenv/config'
import { Worker } from 'node:worker_threads'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { logger } from './logger.mjs'
import { WhatsAppClient } from 'easy-baileys'

const log = logger()

const getSocket = async () => {
  try {
    const customOptions = {
        browser: ["Ubuntu", "Chrome", "20.0.04"],
        printQRInTerminal: false,
        mobile: false,
    }

    const client = await WhatsAppClient.create('multi', './baileys_auth', customOptions)
    const sock = await client.getSocket()

    await new Promise(resolve => setTimeout(resolve, 5000))

    const number = Number(`58${process.env.WHATSAPP_PHONE}`)
    const code = await client.getPairingCode(number)
    log.status('Codigo de conexion a WhatsApp: ', code)
    return sock
  } catch (error) {
    console.error('Error iniciando el cliente de WhatsApp:', error.message)
  }
}

const jid = phone => `58${phone}@s.whatsapp.net`

async function run() {
  try {
    const socket = await getSocket()
    const filePath = fileURLToPath(import.meta.url)
    const folder = dirname(filePath)
    
    const filesWorker = new Worker(join(folder, 'files.mjs'))
    
    filesWorker.on('message', (data) => {
      const { file, phone } = data
      const buffer = Buffer.from(file)
      const caption = 'Comprobante - ' + process.env.OWNER_NAME;
      socket.sendDocument(jid(phone), buffer, 'Comprobante.pdf', 'application/pdf', caption)
    })
    
    const cronWorker = new Worker(join(folder, 'cron.mjs'), { argv: process.argv })
    
    cronWorker.on('message', (data) => {
      const { text, phone } = data
      socket.sendTextMessage(jid(phone), text)
    })
  } catch (error) {
    log.error('Error al iniciar notifs, reintentando...')
    log.error(error)
    run()
  }
}

run().catch(err => {
  log.error('Unhandled error')
  log.error(err)
})
