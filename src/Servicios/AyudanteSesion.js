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

    if (permisos) {
      permisos['vistas.vender'] = permisos['ventas.registrar_venta'] 
        || permisos['ventas.registrar_cuenta']
        || permisos['ventas.registrar_apartado']
        || permisos['ventas.registrar_cotiza']
    }

    return permisos
  },

  can() {
    return function (permiso) {
      const permisos = AyudanteSesion.permisos()
      return permisos?.[permiso] ?? false
    }
  },
}

export default AyudanteSesion
