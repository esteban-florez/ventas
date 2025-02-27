import { makeWASocket, useMultiFileAuthState } from '@whiskeysockets/baileys'

const isInit = process.argv[2] === 'init'

async function init() {
  if (this.socket !== null) return
  
  console.log('-----------------filling socket-----------------')

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
        this.socket = socket
        resolve()
      }
  
      if (connection === 'close') {
        this.socket = null
        console.log('Desconectado...')
        init()
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

async function close() {
  try {
    this.socket.ws.close()
  } catch (error) {
    console.log('Disconnect: ', error)
  }
}

const WhatsApp = {
  socket: null,
  connect: init,
  message: sendMessage,
  file: sendFile,
  disconnect: close,
}

export { WhatsApp }

if (isInit) {
  init.call(WhatsApp)
}
