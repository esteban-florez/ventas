import App from '@/App.vue'

const AyudanteSesion = {
  usuario() {
    const { usuario } = App.methods.extraerDatos()
    return usuario
  },

  /**
   * @return {Record<keyof typeof import('./../consts.js').PERMISOS, boolean>}
   */
  permisos() {
    const { permisos } = App.methods.extraerDatos()
    return permisos
  },
}

export default AyudanteSesion
