import { makeWASocket, useMultiFileAuthState } from '@whiskeysockets/baileys'

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
    console.log('Reusando conexión...')
    return
  }

  console.log('Creando nuevo socket...')

  const { state, saveCreds } = await useMultiFileAuthState('./baileys_auth')
  const socket = makeWASocket({
    auth: state,
    printQRInTerminal: true,
    keepAliveIntervalMs: 10000,
  })

  socket.ev.on('creds.update', saveCreds)

  return new Promise(resolve => {
    socket.ev.on('connection.update', (update) => {
      const { connection } = update
  
      if (connection === 'open') {
        console.log('¡Conexión establecida con WhatsApp!')
        WhatsApp.socket = socket
        resolve(WhatsApp)
      }
  
      if (connection === 'close') {
        WhatsApp.socket = null
        console.log('Desconectado...')
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
    console.error('Error al enviar mensaje:', error)
    throw error
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
    console.error('Error al enviar mensaje:', error)
    throw error
  }
}

