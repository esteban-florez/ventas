<template>
  <section>
    <h1 class="title is-1">Editar rol</h1>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item tag='router-link' to="/roles">Roles</b-breadcrumb-item>
      <b-breadcrumb-item active>Editar rol</b-breadcrumb-item>
    </b-breadcrumb>
    <form-rol :rol="datosRol" @registrar="onEditar" v-if="datosRol" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import HttpService from '../../Servicios/HttpService'
import FormRol from './FormRol'

export default {
  name: 'EditarRol',
  components: { FormRol },

  data: () => ({
    cargando: false,
    datosRol: null
  }),

  async mounted() {
    this.cargando = true
    const rol = await HttpService.obtenerConConsultas('roles.php', {
      accion: 'obtener_por_id',
      id: this.$route.params.id
    })

    this.datosRol = {
      id: this.$route.params.id,
      nombre: rol.nombre,
      permisos: JSON.parse(rol.permisos)
    }

    this.cargando = false
  },

  methods: {
    async onEditar(datosRol) {
      this.cargando = true
      const resultado = await HttpService.editar('roles.php', {
        accion: 'editar',
        rol: {
          id: this.$route.params.id,
          nombre: datosRol.nombre,
          permisos: datosRol.permisos,
        }
      })

      if (resultado) {
        this.cargando = false
        this.$buefy.toast.open({
          type: 'is-info',
          message: 'Información de rol actualizada con éxito.'
        })
        this.$router.push({ name: 'RolesComponent' })
      }
    }
  }
}
</script>
