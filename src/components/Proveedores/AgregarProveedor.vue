<template>
  <section>
    <h1 class="title is-1">Agregar proveedor</h1>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item tag='router-link' to="/proveedores">Proveedores</b-breadcrumb-item>
      <b-breadcrumb-item active>Agregar proveedor</b-breadcrumb-item>
    </b-breadcrumb>
    <form-proveedor :proveedor="datosProveedor" @registrar="onAgregar" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import HttpService from '../../Servicios/HttpService'
import FormProveedor from './FormProveedor'

export default {
  name: 'AgregarProveedor',
  components: { FormProveedor },

  data: () => ({
    cargando: false,
  }),

  methods: {
    async onAgregar(datosProveedor) {
      this.cargando = true
      const resultado = await HttpService.registrar('proveedores.php', {
        accion: 'registrar',
        proveedor: datosProveedor
      })

      if (resultado) {
        this.cargando = false
        this.$buefy.toast.open({
          type: 'is-info',
          message: 'Proveedor registrado con éxito.'
        })
        this.$router.push({ name: 'ProveedoresComponent' })
      }
    }
  }
}
</script>
