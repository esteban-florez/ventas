<template>
  <section>
    <h1 class="title is-1">Editar proveedor</h1>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item tag='router-link' to="/proveedores">Proveedores</b-breadcrumb-item>
      <b-breadcrumb-item active>Editar proveedor</b-breadcrumb-item>
    </b-breadcrumb>
    <form-proveedor :proveedor="datosProveedor" @registrar="onEditar" v-if="datosProveedor" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import HttpService from '../../Servicios/HttpService'
import FormProveedor from './FormProveedor'

export default {
  name: 'EditarProveedor',
  components: { FormProveedor },

  data: () => ({
    cargando: false,
    datosProveedor: null
  }),

  async mounted() {
    this.cargando = true
    const proveedor = await HttpService.obtenerConConsultas('proveedores.php', {
      accion: 'obtener_por_id',
      id: this.$route.params.id
    })
    this.datosProveedor = proveedor
    this.cargando = false
  },

  methods: {
    async onEditar(datosProveedor) {
      this.cargando = true
      const resultado = await HttpService.editar('proveedores.php', {
        accion: 'editar',
        proveedor: datosProveedor
      })

      if (resultado) {
        this.cargando = false
        this.$buefy.toast.open({
          type: 'is-info',
          message: 'Información de proveedor actualizada con éxito.'
        })
        this.$router.push({ name: 'ProveedoresComponent' })
      }
    }
  }
}
</script>
