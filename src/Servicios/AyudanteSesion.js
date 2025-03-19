import App from "@/App.vue"

const AyudanteSesion = {
  obtenerDatosSesion() {
    const { usuario } = App.methods.extraerDatos()
    return usuario
  },
}

export default AyudanteSesion
