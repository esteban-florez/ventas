<template>
  <section>
    <p class="title is-1">Agregar método de pago</p>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item tag='router-link' to="/metodos">Métodos de pago</b-breadcrumb-item>
      <b-breadcrumb-item active>Agregar método de pago</b-breadcrumb-item>
    </b-breadcrumb>
    <form-metodo @submit="onRegistrar" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>

<script>
import FormMetodo from './FormMetodo'
import HttpService from '../../Servicios/HttpService'

export default {
  name: 'AgregarMetodo',
  components: { FormMetodo },

  data: () => ({
    cargando: false,
  }),

  methods: {
    onRegistrar(metodo) {
      this.cargando = true
      let payload = { accion: 'registrar', metodo }

      HttpService.registrar('metodos.php', payload)
        .then(registrado => {
          if (registrado) {
            this.cargando = false
            this.$buefy.toast.open({
              type: 'is-info',
              message: 'Método de pago registrado con éxito.'
            })
          }
        })
    }
  }

}
</script>
