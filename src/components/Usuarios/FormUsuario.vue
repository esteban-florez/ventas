<template>
  <form @submit.prevent="registrar">
    <b-field label="Nombre de usuario">
      <b-input icon="shield-account" type="text" placeholder="Ej. Miguel" v-model="datosUsuario.usuario" required></b-input>
    </b-field>
    <b-field label="Nombre completo">
      <b-input icon="account" type="text" placeholder="Ej. Miguel Hernandez"
        v-model="datosUsuario.nombre" required></b-input>
    </b-field>
    <b-field label="Teléfono del usuario">
      <b-input step="any" icon="phone" type="number" placeholder="Ej. 04120001234"
        v-model="datosUsuario.telefono" required></b-input>
    </b-field>
    <b-field label="Rol del usuario" v-if="!editar || !(datosUsuario.id == 1)">
        <b-select class="wide" placeholder="Seleccionar..." icon="tag-multiple" v-model="datosUsuario.idRol" required>
          <option v-for="rol in roles" :key="rol.id" :value="rol.id">
            {{ rol.nombre }}
          </option>
        </b-select>
      </b-field>
    <b-field label="Contraseña" v-if="!editar">
      <b-input step="any" icon="phone" type="password" placeholder="Introduce la contraseña"
        v-model="datosUsuario.password" minlength="8" maxlength="20" required></b-input>
    </b-field>
    <b-field label="Confirmar contraseña" v-if="!editar">
      <b-input step="any" icon="phone" type="password" placeholder="Introduce nuevamente la contraseña"
        v-model="datosUsuario.confirmacion" minlength="8" maxlength="20" required></b-input>
    </b-field>
    <div class="buttons has-text-centered">
      <b-button type="is-primary" size="is-large" icon-left="check" native-type="submit">Registrar</b-button>
      <b-button type="is-dark" size="is-large" icon-left="cancel" tag="router-link" to="/usuarios">Cancelar</b-button>
    </div>
  </form>
</template>
<script>
import AyudanteSesion from '@/Servicios/AyudanteSesion';
import HttpService from '@/Servicios/HttpService';


export default {
  name: "FormUsuario",
  props: ["usuario", "editar"],

  data: () => ({
    datosUsuario: {
      usuario: "",
      nombre: "",
      telefono: "",
      password: "",
      confirmacion: "",
      idRol: null,
    },
    roles: [],
  }),

  async mounted() {
    console.log(AyudanteSesion.usuario())
    this.datosUsuario = this.usuario

    const roles = await HttpService.obtenerConConsultas('roles.php', { accion: 'obtener' })
    this.roles = roles
  },

  methods: {
    registrar() {
      this.$emit("registrar", this.datosUsuario)
    }
  }
}
</script>
