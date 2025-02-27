import { makeWASocket, useMultiFileAuthState } from '@whiskeysockets/baileys'

async function init() {
  if (this.socket !== null) return

  const { state, saveCreds } = await useMultiFileAuthState('./baileys_auth')
  const socket = makeWASocket({
    auth: state,
    printQRInTerminal: true,
    keepAliveIntervalMs: 10000,
  })

  socket.ev.on('creds.update', saveCreds)

  return new Promise((resolve, reject) => {
    socket.ev.on('connection.update', (update) => {
      const { connection } = update
  
      if (connection === 'open') {
        console.log('¡Conexión establecida con WhatsApp!')
        this.socket = socket
        resolve()
      }
  
      if (connection === 'close') {
        console.log('Desconectado...')
        this.socket = null
        reject()
      }
    })
  })
}

async function sendMessage(chatId, message) {
  try {
    const id = `58${chatId}@s.whatsapp.net`
    await this.socket.sendMessage(id, { text: message })
  } catch (error) {
    console.error('Error al enviar mensaje:', error)
    throw error
  }
}

const WhatsApp = { socket: null }

WhatsApp.connect = init.bind(WhatsApp),
WhatsApp.message = sendMessage.bind(WhatsApp)

export { WhatsApp }

