<template>
  <section>
    <h1 class="title is-1">Agregar rol</h1>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item tag='router-link' to="/roles">Roles</b-breadcrumb-item>
      <b-breadcrumb-item active>Agregar rol</b-breadcrumb-item>
    </b-breadcrumb>
    <form-rol @registrar="onAgregar" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import HttpService from '../../Servicios/HttpService'
import FormRol from './FormRol'

export default {
  name: 'AgregarRol',
  components: { FormRol },

  data: () => ({
    cargando: false,
  }),

  methods: {
    async onAgregar(datosRol) {
      this.cargando = true
      const resultado = await HttpService.registrar('roles.php', {
        accion: 'registrar',
        rol: datosRol
      })

      if (resultado) {
        this.cargando = false
        this.$buefy.toast.open({
          type: 'is-info',
          message: 'Rol registrado con éxito.'
        })
        this.$router.push({ name: 'RolesComponent' })
      }
    }
  }
}
</script>
