import {
  makeWASocket,
  Browsers,
  DisconnectReason,
  fetchLatestBaileysVersion,
  makeCacheableSignalKeyStore,
  useMultiFileAuthState,
} from "@whiskeysockets/baileys"
import { logger } from "./logger.mjs"

const log = logger()

const jid = (phone) => {
  const result = phone[0] === "0" ? "58" + phone.slice(1) : phone
  return `${result}@s.whatsapp.net`
}

const MANUAL_DISCONNECT = "manual-disconnect"

const WhatsApp = {
  sock: null,
  message: sendMessage,
  file: sendFile,
  disconnect: close,
}

/**
 * @returns {Promise<typeof WhatsApp>}
 */
export async function connect(phone) {
  if (WhatsApp.sock !== null) {
    log.status("Conectando a WhatsApp, reusando socket...")
    return WhatsApp
  }

  log.status("Conectando a WhatsApp, creando nuevo socket...")

  try {
    let { state, saveCreds } = await useMultiFileAuthState("./baileys_auth")
    let { version } = await fetchLatestBaileysVersion()
    const conn = makeWASocket({
      version,
      logger: log,
      printQRInTerminal: false,
      browser: Browsers.ubuntu("Chrome"),
      keepAliveIntervalMs: 30000,
      defaultQueryTimeoutMs: undefined,
      connectTimeoutMs: 60000,
      auth: {
        creds: state.creds,
        keys: makeCacheableSignalKeyStore(state.keys, log),
      },
    })

    conn.ev.on("creds.update", saveCreds)

    if (!conn.authState.creds.registered) {
      setTimeout(async () => {
        const code = await conn.requestPairingCode(phone)
        console.log("Pairing Code:", code)
      }, 3000)
    }

    conn.ev.process(async (events) => {
      if (events["connection.update"]) {
        const update = events["connection.update"]
        const { connection, lastDisconnect } = update

        if (connection === "open") {
          log.status("Conexion establecida con WhatsApp")
          WhatsApp.sock = conn
        }

        if (connection === "close") {
          log.status("Conexion cerrada")
          WhatsApp.sock = null

          const shouldReconnect = lastDisconnect?.error
            ? lastDisconnect.error.output?.statusCode !==
              DisconnectReason.loggedOut
            : false

          if (lastDisconnect?.error?.message === MANUAL_DISCONNECT) return

          if (shouldReconnect) {
            log.status("Reconectando...")
            connect(phone)
          } else {
            log.status("Connection closed. You are logged out.")
          }
        }
      }
    })

    return new Promise((resolve) => {
      const checkConnection = () => {
        if (WhatsApp.sock) {
          resolve(WhatsApp)
        } else {
          setTimeout(checkConnection, 100)
        }
      }
      checkConnection()
    })
  } catch (error) {
    console.error("Error iniciando el cliente de WhatsApp:", error.message)
    throw error
  }
}

/**
 * @param {string} chatId
 * @param {string} message
 */
async function sendMessage(chatId, message) {
  try {
    const phone = jid(chatId)
    await this.sock.sendMessage(phone, { text: message })
    log.status(`Mensaje enviado a 0${chatId} de forma exitosa`)
  } catch (error) {
    log.error("Error al enviar mensaje")
    log.error(error)
    throw error
  }
}

/**
 * @param {string} chatId
 * @param {Buffer} file
 * @param {string} name
 * @param {string} caption
 */
async function sendFile(chatId, file, name, caption = "") {
  try {
    const phone = jid(chatId)
    await this.sock.sendMessage(phone, {
      document: file,
      fileName: name,
      mimetype: "application/pdf",
      caption: caption,
    })
    log.status("Archivo enviado exitosamente")
  } catch (error) {
    log.error("Error al enviar el archivo")
    log.error(error)
    throw error
  }
}

async function close() {
  if (!this.sock) return
  log.status("Cerrando conexion manualmente...")
  await this.sock.end({ message: MANUAL_DISCONNECT })
  this.sock = null
}
