<template>
  <section>
    <p class="title is-1">Agregar usuario</p>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item tag='router-link' to="/usuarios">Usuarios</b-breadcrumb-item>
      <b-breadcrumb-item active>Agregar usuario</b-breadcrumb-item>
    </b-breadcrumb>
    <form-usuario @registrar="onRegistrar" :usuario="datosUsuario" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import FormUsuario from './FormUsuario'
import HttpService from '../../Servicios/HttpService'

export default {
  name: "AgregarUsuario",
  components: { FormUsuario },

  data: () => ({
    cargando: false,
    datosUsuario: {
      usuario: "",
      nombre: "",
      telefono: "",
      password: "",
      confirmacion: "",
    }
  }),

  methods: {
    async onRegistrar(usuario) {
      this.cargando = true
      let payload = {
        accion: 'registrar',
        usuario: usuario
      }

      const { registrado, mensaje } = await HttpService.registrar('usuarios.php', payload)
      this.cargando = false

      const alerta = {
        type: registrado ? 'is-info' : 'is-danger',
        message: mensaje,
      }

      this.$buefy.toast.open(alerta)

      if (registrado) {
        this.$router.push({ name: 'UsuariosComponent' })
      }
    }
  }

}
</script>
