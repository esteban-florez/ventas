import App from '@/App.vue'

const AyudanteSesion = {
  usuario() {
    const { usuario } = App.methods.extraerDatos()
    return usuario
  },

  permisos() {
    const { permisos } = App.methods.extraerDatos()
    return permisos
  },
}

export default AyudanteSesion
