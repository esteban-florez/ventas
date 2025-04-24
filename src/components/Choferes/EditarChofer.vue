<template>
  <section>
    <h1 class="title is-1">Editar chofer</h1>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item tag='router-link' to="/choferes">Choferes</b-breadcrumb-item>
      <b-breadcrumb-item active>Editar chofer</b-breadcrumb-item>
    </b-breadcrumb>
    <form-chofer :chofer="datosChofer" @registrar="onEditar" :formatoMonto="formatoMonto" v-if="datosChofer" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import HttpService from '../../Servicios/HttpService'
import FormChofer from './FormChofer'
import Utiles from '../../Servicios/Utiles'

export default {
  name: "EditarChofer",
  components: { FormChofer },

  data: () => ({
    cargando: false,
    datosChofer: null
  }),

  async mounted() {
    this.cargando = true
    const chofer = await HttpService.obtenerConConsultas('choferes.php', {
      accion: 'obtener_por_id',
      id: this.$route.params.id
    })

    this.datosChofer = chofer
    this.cargando = false
  },

  methods: {
    async onEditar(datosChofer) {
      this.cargando = true
      const resultado = await HttpService.editar('choferes.php', {
        accion: 'editar',
        chofer: datosChofer
      })

      if (resultado) {
        this.cargando = false
        this.$buefy.toast.open({
          type: 'is-info',
          message: 'Información de chofer actualizada con éxito.'
        })
        this.$router.push({ name: 'ChoferesComponent' })
      }
    },
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
    }
  }
}
</script>
