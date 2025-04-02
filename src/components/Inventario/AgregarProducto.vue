<template>
  <section>
    <p class="title is-1">Agregar productos</p>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item tag='router-link' to="/inventario">Inventario</b-breadcrumb-item>
      <b-breadcrumb-item active>Agregar producto</b-breadcrumb-item>
    </b-breadcrumb>
    <form-producto @registrado="onRegistrado" :editar="false" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import FormProducto from './FormProducto.vue'
import HttpService from '../../Servicios/HttpService'
import AyudateSesion from '@/Servicios/AyudanteSesion'

export default {
  name: 'AgregarProducto',
  components: { FormProducto },

  data: () => ({
    cargando: false,
  }),

  methods: {
    onRegistrado(producto) {
      console.log(producto)
      this.cargando = true

      const precioCompra = Number(producto.precioCompra) * 100
      const monto = (Number(producto.existencia) * precioCompra) / 100

      let payload = {
        accion: 'registrar',
        producto: {
          monto,
          usuario: AyudateSesion.usuario().id,
          ...producto,
        }
      }

      HttpService.registrar('productos.php', payload)
        .then(registrado => {
          this.cargando = false

          if (registrado) {
            this.$buefy.toast.open({
              type: 'is-info',
              message: 'Producto registrado con éxito.'
            })

            return
          }

          this.$buefy.toast.open({
            type: 'is-danger',
            message: 'Error al registrar producto.'
          })
        })
    }
  }

}
</script>
