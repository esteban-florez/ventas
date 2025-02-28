import { Worker } from 'node:worker_threads'
import { connect } from './whatsapp.mjs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'

const WhatsApp = await connect()

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

const filesWorker = new Worker(join(__dirname, 'files.mjs'))

filesWorker.on('message', (data) => {
  const { file, phone } = data
  const buffer = Buffer.from(file)
  WhatsApp.file(phone, buffer)
})

const cronWorker = new Worker(join(__dirname, 'cron.mjs'))

cronWorker.on('message', (data) => {
  const { text, phone } = data
  WhatsApp.message(phone, text)
})
