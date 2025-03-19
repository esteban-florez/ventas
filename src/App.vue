<template>
  <div>
    <encabezado-component :usuario="usuario" @sesion="obtenerUsuario" />
    <div :class="{ 'container': usuario }">
      <router-view @sesion="obtenerUsuario" />
    </div>
  </div>
</template>

<script>
  import EncabezadoComponent from './components/EncabezadoComponent'
  import HttpService from './Servicios/HttpService'

  export default {
    name: 'App',

    components: {
      EncabezadoComponent,
    },

    data: () => ({
      usuario: null,
      permisos: null,
    }),

    mounted() {
      this.obtenerUsuario()
    },

    methods: {
      async obtenerUsuario() {
        const { usuario, permisos } = await HttpService.obtenerConConsultas('usuarios.php', {
          accion: 'estado_autenticacion'
        })

        this.usuario = usuario
        this.permisos = permisos
      },

      extraerDatos() {
        return {
          usuario: this.usuario,
          permisos: this.permisos,
        }
      }
    },
  }
</script>
