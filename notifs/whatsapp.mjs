import { WhatsAppClient } from 'easy-baileys'
import { logger } from './logger.mjs'

const log = logger()
const jid = phone => `58${phone}@s.whatsapp.net`
const MANUAL_DISCONNECT = 'manual-disconnect'

const customOptions = {
  browser: ["Ubuntu", "Chrome", "20.0.04"],
  printQRInTerminal: false,
  mobile: false,
  logger: log,
}

const WhatsApp = {
  sock: null,
  message: sendMessage,
  file: sendFile,
  disconnect: close,
}

/**
 * @returns {Promise<typeof WhatsApp>}
 */
export async function connect() {
  if (WhatsApp.sock !== null) {
    log.status('Conectando a WhatsApp, reusando socket...')
    return WhatsApp
  }

  log.status('Conectando a WhatsApp, creando nuevo socket...')

  try {
    const client = await WhatsAppClient.create('multi', './baileys_auth', customOptions)
    const sock = await client.getSocket()
    const paired = sock.authState.creds.registered

    return new Promise(resolve => {
      sock.ev.on('connection.update', async (event) => {
        const { connection, qr, lastDisconnect } = event

        if (!paired && qr) {
          const number = Number(`58${process.env.WHATSAPP_PHONE}`)
          const code = await client.getPairingCode(number)
          const formatted = `${code.slice(0, 4)}-${code.slice(4)}`
          log.status(`Codigo de conexion a WhatsApp: ${formatted}`)
        }

        if (connection === 'open') {
          log.status('Conexion establecida con WhatsApp')
          WhatsApp.sock = sock
          resolve(WhatsApp)
        }

        if (connection === 'close') {
          log.status('Conexion cerrada')
          WhatsApp.sock = null

          const message = lastDisconnect?.error?.message
          if (message === MANUAL_DISCONNECT) return

          const status = lastDisconnect?.error?.output?.statusCode
          if (status === 515 && message.includes('restart required')) {
            log.status('Sesion iniciada, reiniciando...')
            resolve(await connect())
            return
          }

          log.status('Reconectando...')
          connect()
        }
      })
    })
  } catch (error) {
    console.error('Error iniciando el cliente de WhatsApp:', error.message)
  }
}


/**
 * @param {string} chatId
 * @param {string} message
 */
async function sendMessage(chatId, message) {
  try {
    const phone = jid(chatId)
    await this.sock.sendTextMessage(phone, message)
    log.status(`Mensaje enviado a 0${chatId} de forma exitosa`)
  } catch (error) {
    log.error('Error al enviar mensaje')
    log.error(error)
  }
}

/**
 * @param {string} chatId
 * @param {Buffer} file
 * @param {string} name
 * @param {string} caption
 */
async function sendFile(chatId, file, name, caption = '') {
  try {
    const PDF_MIMETYPE = 'application/pdf'
    const phone = jid(chatId)
    await this.sock.sendDocument(phone, file, name, PDF_MIMETYPE, caption)
    log.status('Archivo enviado exitosamente')
  } catch (error) {
    log.error('Error al enviar el archivo')
    log.error(error)
  }
}

async function close() {
  if (!this.sock) return
  log.status('Cerrando conexion manualmente...')
  await this.sock.end({ message: MANUAL_DISCONNECT })
  this.sock = null
}
