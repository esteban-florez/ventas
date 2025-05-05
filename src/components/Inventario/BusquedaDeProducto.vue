<template>
  <section>
    <b-field label="Nombre o código del producto">
      <b-autocomplete
        v-model="producto"
        id="producto"
        placeholder="Escribe el nombre o el código de barras del producto"
        :keep-first="true"
        :data="productosFiltrados"
        field="nombre"
        @input="buscarProductos"
        @select="seleccionarProducto"
      >
        <template slot-scope="props">
          <div>
            <span>{{ props.option.nombre }}</span>
            <span v-if="props.option.precioVenta !== undefined" class="has-text-grey is-size-7">
              &mdash; Precio: ${{ formatoMonto(props.option.precioVenta) }}
            </span>
          </div>
        </template>
      </b-autocomplete>
    </b-field>
    <div class="notification is-info mt-2" v-if="productoSeleccionado">
      <button class="delete" @click="deseleccionarProducto"></button>
      <p>Producto: <b>{{ productoSeleccionado.nombre }}</b></p>
      <p v-if="productoSeleccionado.codigo">Código: <b>{{ productoSeleccionado.codigo }}</b></p>
    </div>
  </section>
</template>
<script>
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'

export default {
  name: "BusquedaDeProducto",
  props: {
    initialProducto: {
      type: Object,
      default: () => null
    }
  },
  data: () => ({
    producto: "",
    productosEncontrados: [],
    productoSeleccionado: null
  }),
  methods: {
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
    },
    deseleccionarProducto() {
      this.productoSeleccionado = null
      this.$emit('deseleccionado')
    },
    seleccionarProducto(opcion) {
      if (!opcion) return
      this.productoSeleccionado = opcion
      this.$emit("seleccionado", this.productoSeleccionado)
      setTimeout(() => this.producto = '', 10)
    },
    buscarProductos() {
      let payload = {
        accion: 'obtener_nombre_codigo',
        producto: this.producto
      }
      HttpService.obtenerConConsultas('productos.php', payload)
        .then(productos => {
          this.productosEncontrados = productos
        })
    },
  },
  watch: {
    initialProducto: {
      immediate: true,
      handler(newVal) {
        if (newVal && newVal.nombre) {
          this.productoSeleccionado = newVal;
          this.producto = newVal.nombre;
        }
      }
    }
  },
  computed: {
    productosFiltrados() {
      return this.productosEncontrados.filter(opcion => {
        return (
          opcion.nombre.toString().toLowerCase().indexOf(this.producto.toLowerCase()) >= 0 ||
          opcion.codigo.toString().indexOf(this.producto) >= 0
        )
      })
    }
  }
}
</script>