<template>
  <section>
    <p class="title is-1">Editar método de pago</p>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item tag='router-link' to="/metodos">Métodos de pago</b-breadcrumb-item>
      <b-breadcrumb-item active>Editar método de pago</b-breadcrumb-item>
    </b-breadcrumb>
    <form-metodo @submit="editar" :editar="true" @cargando="onCargando" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>

<script>
import FormMetodo from './FormMetodo'
import HttpService from '../../Servicios/HttpService'

export default {
  name: 'EditarMetodo',
  components: { FormMetodo },

  data: () => ({
    cargando: false,
  }),

  methods: {
    async editar(campos) {
      this.cargando = true
      const resultado = await HttpService.editar('metodos.php', {
        accion: 'editar',
        metodo: campos,
        id: this.$route.params.id
      })

      if (resultado) {
        this.cargando = false
        this.$buefy.toast.open({
          type: 'is-info',
          message: 'Método de pago actualizado con éxito.'
        })

        this.$router.push({ name: 'MetodosComponent' })
      }
    },

    onCargando(valor) {
      this.cargando = valor
    }
  }

}
</script>
