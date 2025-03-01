import pino from 'pino'
import { makeWASocket, useMultiFileAuthState } from '@whiskeysockets/baileys'
import { log, options } from './logger.mjs'

let reconnections = 0

const WhatsApp = {
  socket: null,
  message: sendMessage,
  file: sendFile,
}

/**
 * @returns {Promise<typeof WhatsApp>}
 */
export async function connect() {
  if (WhatsApp.socket !== null) {
    log.info('Conectando a WhatsApp, reusando socket...')
    return
  }

  if (reconnections >= 20) {
    throw new Error('Mas de 20 reconexiones a WhatsApp')
  }

  reconnections++
  log.info('Conectando a WhatsApp, creando nuevo socket...')

  const { state, saveCreds } = await useMultiFileAuthState('./baileys_auth')
  const socket = makeWASocket({
    auth: state,
    printQRInTerminal: true,
    keepAliveIntervalMs: 10000,
    logger: pino({ ...options, level: 'error' }),
  })

  socket.ev.on('creds.update', saveCreds)

  return new Promise(resolve => {
    socket.ev.on('connection.update', (update) => {
      const { connection } = update
  
      if (connection === 'open') {
        log.info('Conexión establecida con WhatsApp')
        WhatsApp.socket = socket
        resolve(WhatsApp)
      }
  
      if (connection === 'close') {
        WhatsApp.socket = null
        log.info('Conexion cerrada. Reconectando a WhatsApp...')
        connect()
      }
    })
  })
}

/**
 * @param {string} chatId
 * @param {string} message
 */
async function sendMessage(chatId, message) {
  try {
    const id = `58${chatId}@s.whatsapp.net`
    await this.socket.sendMessage(id, { text: message })
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
  } catch (error) {
    log.error('Error al enviar el archivo')
    log.error(error)
  }
}
