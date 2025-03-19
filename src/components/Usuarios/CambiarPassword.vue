<template>
  <section>
  <h1 class="title is-1">Cambiar contraseña</h1>
  <form @submit.prevent="confirmar" ref="form">
    <b-field label="Escribe la contraseña actual">
      <b-input type="password" placeholder="Contraseña actual" password-reveal v-model="datos.oldPassword" required>
      </b-input>
    </b-field>
    <b-field label="Escribe la contraseña nueva"
      message="Al menos un número, Al menos una mayúscula, Al menos una minúscula, Mínimo 8 caracteres, Máximo 20 caracteres">
      <b-input type="password" placeholder="Contraseña nueva" password-reveal v-model="datos.password" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,20}" minlength="8" maxlength="20" required>
      </b-input>
    </b-field>
    <b-field label="Repite la nueva contraseña">
      <b-input type="password" placeholder="Repite nueva contraseña" password-reveal
        v-model="datos.confirmation" required>
      </b-input>
    </b-field>
    <div class="buttons has-text-centered">
      <b-button type="is-primary" size="is-large" icon-left="check" native-type="submit">Cambiar contraseña</b-button>
      <b-button type="is-dark" size="is-large" icon-left="cancel" tag="router-link" to="/perfil">Cancelar</b-button>
    </div>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </form>
  </section>
</template>
<script>
import AyudanteSesion from '../../Servicios/AyudanteSesion'
import HttpService from '../../Servicios/HttpService'

export default {
  name: 'CambiarPassword',

  data: () => ({
    datos: {
      oldPassword: '',
      password: '',
      confirmation: '',
    },
    cargando: false
  }),

  methods: {
    async confirmar() {
      this.$buefy.dialog.confirm({
        message: '¿Seguro que deseas cambiar la contraseña?',
        confirmText: 'Sí, cambiar',
        cancelText: 'Cancelar',
        onConfirm: this.enviar,
      })
    },

    async enviar() {
      this.cargando = true

      const { registrado, mensaje } = await HttpService.registrar('usuarios.php', {
        accion: 'cambiar_password',
        idUsuario: AyudanteSesion.obtenerDatosSesion().id,
        datos: this.datos,
      })

      this.cargando = false

      this.$buefy.toast.open({
        type: registrado ? 'is-info' : 'is-danger',
        message: mensaje,
      })

      this.$refs.form.reset()

      if (registrado) {
        this.$router.push({
          name: 'PerfilComponent',
        })
      }
    },
  }
}
</script>
