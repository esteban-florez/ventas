import "dotenv/config"
import { connect } from "./whatsapp.mjs"

const phoneNumber = process.env.WHATSAPP_PHONE
const WhatsApp = await connect(phoneNumber)

WhatsApp.message(phoneNumber, "Sesión iniciada exitosamente.")
