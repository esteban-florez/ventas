import 'dotenv/config'
import { connect } from './whatsapp.mjs'

const WhatsApp = await connect()

WhatsApp.message(process.env.WHATSAPP_PHONE, 'Sesión iniciada exitosamente.')
