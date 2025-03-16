import 'dotenv/config'
import { Worker } from 'node:worker_threads'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { logger } from './logger.mjs'
import { connect } from './whatsapp.mjs'

const log = logger()

async function run() {
  try {
    const WhatsApp = await connect()
    const filePath = fileURLToPath(import.meta.url)
    const folder = dirname(filePath)
    
    const filesWorker = new Worker(join(folder, 'files.mjs'))
    
    filesWorker.on('message', (data) => {
      const { file, phone, name, caption } = data
      const buffer = Buffer.from(file)
      WhatsApp.file(phone, buffer, name, caption)
    })
    
    const cronWorker = new Worker(join(folder, 'cron.mjs'), { argv: process.argv })
    
    cronWorker.on('message', (data) => {
      const { text, phone } = data
      WhatsApp.message(text, phone)
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
