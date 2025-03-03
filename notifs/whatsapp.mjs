import { makeWASocket, useMultiFileAuthState } from '@whiskeysockets/baileys'
import { logger } from './logger.mjs'

const log = logger()

const WhatsApp = {
  socket: null,
  message: sendMessage,
  file: sendFile,
}

const HOURLY = 1000 * 60 * 60
const FIVE_MIN = 1000 * 60 * 5

setInterval(close, HOURLY)

/**
 * @returns {Promise<typeof WhatsApp>}
 */
export async function connect() {
  if (WhatsApp.socket !== null) {
    log.status('Conectando a WhatsApp, reusando socket...')
    return
  }

  log.status('Conectando a WhatsApp, creando nuevo socket...')

  const { state, saveCreds } = await useMultiFileAuthState('./baileys_auth')
  const socket = makeWASocket({
    auth: state,
    printQRInTerminal: true,
    keepAliveIntervalMs: 10000,
    logger: log,
  })

  socket.ev.on('creds.update', saveCreds)

  return new Promise(resolve => {
    socket.ev.on('connection.update', (update) => {
      const { connection } = update
  
      if (connection === 'open') {
        log.status('Conexion establecida con WhatsApp')
        WhatsApp.socket = socket
        resolve(WhatsApp)
      }
  
      if (connection === 'close') {
        WhatsApp.socket = null
        log.status('Conexion cerrada. Reconexion programada en 5 minutos...')

        setTimeout(() => {
          log.status('Reconectando...')
          connect()
        }, FIVE_MIN)
      }
    })
  })
}

async function close() {
  log.status('Tiempo límite de conexión alcanzado.')

  if (WhatsApp.socket) {
    log.status('Cerrando conexión manualmente...')
    WhatsApp.socket.ws.close()
    WhatsApp.socket = null
  }
}

/**
 * @param {string} chatId
 * @param {string} message
 */
async function sendMessage(chatId, message) {
  try {
    const id = `58${chatId}@s.whatsapp.net`
    await this.socket.sendMessage(id, { text: message })
    log.status('Mensaje enviado exitosamente')
  } catch (error) {
    log.error('Error al enviar mensaje')
    log.error(error)
  }
}

/**
 * @param {string} chatId
 * @param {Buffer} buffer
 */
async function sendFile(chatId, buffer) {
  try {
    const id = `58${chatId}@s.whatsapp.net`
    await this.socket.sendMessage(id, {
      document: buffer,
      mimetype: 'application/pdf',
      fileName: 'Comprobante.pdf',
    })
    log.status('Archivo enviado exitosamente')
  } catch (error) {
    log.error('Error al enviar el archivo')
    log.error(error)
  }
}
