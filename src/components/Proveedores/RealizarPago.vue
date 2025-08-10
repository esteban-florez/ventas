<template>
  <section>
    <h1 class="title is-1">Realizar pago</h1>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item tag='router-link' to="/proveedores">Proveedores</b-breadcrumb-item>
      <b-breadcrumb-item active>Realizar pago</b-breadcrumb-item>
    </b-breadcrumb>
    <form-pago @registrar="onRegistrar" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import AyudanteSesion from '@/Servicios/AyudanteSesion'
import HttpService from '../../Servicios/HttpService'
import FormPago from './FormPago'

export default {
  name: 'RealizarPago',
  components: { FormPago },

  data: () => ({
    cargando: false,
    proveedor: null,
  }),

  async mounted() {
    this.cargando = true
    const proveedor = await HttpService.obtenerConConsultas('proveedores.php', {
      accion: 'obtener_por_id',
      id: this.$route.params.id
    })

    this.proveedor = proveedor
    this.cargando = false
  },

  methods: {
    async onRegistrar(datosPago) {
      this.cargando = true
      const resultado = await HttpService.registrar('proveedores.php', {
        accion: 'pagar_proveedor',
        pago: {
          monto: datosPago.monto,
          factura: datosPago.factura,
          idProveedor: this.proveedor.id,
          idUsuario: AyudanteSesion.usuario().id,
        }
      })

      if (resultado) {
        this.cargando = false
        this.$buefy.toast.open({
          type: 'is-info',
          message: 'Pago realizado con éxito.'
        })

        this.$router.push({
          name: 'PagosComponent',
          params: { id: this.proveedor.id },
        })
      }
    }
  }
}
</script>
