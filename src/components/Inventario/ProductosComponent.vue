<template>
  <section>
    <nav-component :titulo="'Inventario'" :link="can('productos.registrar') ? { path: '/agregar-producto' } : null" :texto="'Agregar producto'" />
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Inventario</b-breadcrumb-item>
    </b-breadcrumb>
    <mensaje-inicial :titulo="'No se han encontrado productos :('"
      :subtitulo="'Agrega productos pulsando el botón de Agregar productos'" v-if="productos.length < 1" />

    <div v-if="productos.length > 0">
      <cartas-totales :totales="cartasTotales" />
      <div class="columns">
        <div class="column">
          <b-select v-model="perPage">
            <option value="5">5 por página</option>
            <option value="10">10 por página</option>
            <option value="15">15 por página</option>
            <option value="20">20 por página</option>
          </b-select>
        </div>
        <div class="column is-flex is-justify-content-end">
          <b-button type="is-primary" tag="a" href="#/pdf/productos" target="__blank" rel="noopener noreferrer">
            Imprimir
          </b-button>
        </div>
      </div>

      <b-table class="box" :data="productos" :paginated="isPaginated" :per-page="perPage"
        :current-page.sync="currentPage" :pagination-simple="isPaginationSimple"
        :pagination-position="paginationPosition" :default-sort-direction="defaultSortDirection"
        :pagination-rounded="isPaginationRounded" :sort-icon="sortIcon" :sort-icon-size="sortIconSize"
        default-sort="user.first_name" aria-next-label="Next page" aria-previous-label="Previous page"
        aria-page-label="Page" aria-current-label="Current page">
        <b-table-column field="codigo" label="Código" sortable searchable v-slot="props">
          {{ props.row.codigo }}
        </b-table-column>

        <b-table-column field="nombre" label="Nombre" sortable searchable v-slot="props">
          {{ props.row.nombre }}
        </b-table-column>

        <b-table-column field="precioCompra" label="Precio compra" sortable v-slot="props">
          ${{ formatoMonto(props.row.precioCompra) }}
        </b-table-column>

        <b-table-column field="precioVenta" label="Precio venta" sortable v-slot="props">
          ${{ formatoMonto(props.row.precioVenta) }}
        </b-table-column>

        <b-table-column field="precioVenta2" label="Precio venta 2" sortable v-slot="props">
          ${{ formatoMonto(props.row.precioVenta2) }}
        </b-table-column>

        <b-table-column field="precioVenta3" label="Precio venta 3" sortable v-slot="props">
          ${{ formatoMonto(props.row.precioVenta3) }}
        </b-table-column>
        
        <b-table-column field="precioVenta4" label="Precio venta 3" sortable v-slot="props">
          ${{ formatoMonto(props.row.precioVenta4) }}
        </b-table-column>

        <b-table-column field="ganancia" label="Ganancia" sortable v-slot="props">
          <b>${{ formatoMonto(props.row.precioVenta - props.row.precioCompra) }}</b>
        </b-table-column>

        <b-table-column field="vendidoMayoreo" label="¿Mayoreo?" sortable v-slot="props">
          <b-tag type="is-danger" v-if="!props.row.vendidoMayoreo">No</b-tag>

          <div v-if="props.row.vendidoMayoreo">
            <b>Precio: </b>${{ formatoMonto(props.row.precioMayoreo) }}<br>
            <b>A partir: </b>{{ props.row.cantidadMayoreo }}
          </div>
        </b-table-column>

        <b-table-column field="existencia" label="Existencia" sortable v-slot="props">
          {{ props.row.existencia }} {{ props.row.unidad }}
        </b-table-column>

        <b-table-column field="nombreProveedor" label="Proveedor" sortable searchable v-slot="props">
          {{ props.row.nombreProveedor }}
        </b-table-column>

        <b-table-column field="agregar" label="Agregar" v-slot="props" v-if="can('productos.agregar_existencia')">
          <b-button type="is-primary" @click="agregarExistencia(props.row)">
            <b-icon icon="plus" />
          </b-button>
        </b-table-column>

        <b-table-column field="remover" label="Retirar" v-slot="props" v-if="can('productos.remover_existencia')">
          <b-button type="is-warning" @click="removerExistencia(props.row)">
            <b-icon icon="minus" />
          </b-button>
        </b-table-column>

        <b-table-column field="editar" label="Editar" v-slot="props" v-if="can('productos.editar')">
          <b-button type="is-info" @click="editar(props.row.id)">
            <b-icon icon="pen" />
          </b-button>
        </b-table-column>

        <b-table-column field="eliminar" label="Eliminar" v-slot="props" v-if="can('productos.eliminar')">
          <b-button type="is-danger" @click="eliminar(props.row.id)">
            <b-icon icon="delete" />
          </b-button>
        </b-table-column>
      </b-table>
    </div>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import HttpService from '../../Servicios/HttpService'
import NavComponent from '../Extras/NavComponent'
import MensajeInicial from '../Extras/MensajeInicial'
import CartasTotales from '../Extras/CartasTotales'
import AyudanteSesion from '../../Servicios/AyudanteSesion'
import Utiles from '../../Servicios/Utiles'

export default {
  name: 'ProductosComponent',
  components: { NavComponent, MensajeInicial, CartasTotales },

  data: () => ({
    productos: [],
    cargando: false,
    isPaginated: true,
    isPaginationSimple: false,
    isPaginationRounded: true,
    paginationPosition: 'bottom',
    defaultSortDirection: 'asc',
    sortIcon: 'arrow-up',
    sortIconSize: 'is-medium',
    currentPage: 1,
    perPage: 5,
    cartasTotales: []
  }),

  mounted() {
    this.obtenerProductos()
  },

  methods: {
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
    },
    agregarExistencia(producto) {
      this.$buefy.dialog.prompt({
        message: '¿Cuántas piezas vas a agregar de ' + producto.nombre + '?',
        cancelText: 'Cancelar',
        confirmText: 'Agregar',
        inputAttrs: {
          type: 'number',
          placeholder: 'Escribe la cantidad de productos',
          value: '',
          step: '0.01',
          min: 0.000,
        },
        trapFocus: true,
        onConfirm: (value) => {
          this.cargando = true
          const precioCompra = Number(producto.precioCompra) * 100
          const monto = (Number(value) * precioCompra) / 100

          HttpService.registrar('productos.php', {
            accion: 'agregar_existencia',
            entrada: {
              cantidad: value,
              id: producto.id,
              usuario: AyudanteSesion.usuario().id,
              monto,
            },
          })
            .then(registrado => {
              if (registrado) {
                this.cargando = false
                this.$buefy.toast.open(value + ' Productos agregados a ' + producto.nombre)
                this.obtenerProductos()
              }
            })

        }
      })
    },

    removerExistencia(producto) {
      this.$buefy.dialog.prompt({
        message: '¿Cuántas unidades (' + producto.unidad + ') vas a retirar de ' + producto.nombre + '?',
        cancelText: 'Cancelar',
        confirmText: 'Retirar',
        inputAttrs: {
          type: 'number',
          placeholder: 'Escribe la cantidad de productos',
          value: '',
          min: 0.000,
          step: 0.01,
          max: producto.existencia,
        },
        trapFocus: true,
        onConfirm: (value) => {
          this.cargando = true
          HttpService.registrar('productos.php', {
            accion: 'remover_existencia',
            producto: {
              cantidad: value,
              id: producto.id,
              usuario: AyudanteSesion.usuario().id,
            },
          })
            .then(registrado => {
              if (registrado) {
                this.cargando = false
                this.$buefy.toast.open(value + ' Productos retirados a ' + producto.nombre)
                this.obtenerProductos()
              }
            })

        }
      })
    },

    async eliminar(idProducto) {
      this.$buefy.dialog.confirm({
        title: 'Eliminar producto',
        message: 'Seguro que quieres <b>eliminar</b> este producto? Esta acción no se puede revertir.',
        confirmText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        type: 'is-danger',
        hasIcon: true,
        onConfirm: () => {
          this.cargando = true
          HttpService.eliminar('productos.php', {
            accion: 'eliminar',
            id: idProducto
          })
            .then(resultado => {
              if (!resultado) {
                this.$buefy.toast.open('Error al eliminar')
                this.cargando = false
                return
              }

              if (resultado) {
                this.cargando = false
                this.$buefy.toast.open({
                  type: 'is-info',
                  message: 'Producto eliminado.'
                })
                this.obtenerProductos()
              }
            })
        }
      })
    },

    editar(idProducto) {
      this.$router.push({
        name: "EditarProducto",
        params: { id: idProducto }
      })
    },

    obtenerProductos() {
      this.cargando = true
      let payload = {
        accion: 'obtener'
      }
      HttpService.obtenerConConsultas('productos.php', payload)
        .then(respuesta => {
          this.productos = respuesta.productos

          this.cartasTotales = [
            { nombre: "Número Productos", total: this.productos.length, icono: "package-variant-closed", clase: "has-text-danger" },
            { nombre: "Total productos", total: respuesta.totalProductos, icono: "chart-bar-stacked", clase: "has-text-primary" },
            { nombre: "Total inventario", total: respuesta.totalInventario, icono: "currency-usd", clase: "has-text-success" },
            { nombre: "Ganancia", total: respuesta.gananciaInventario, icono: "currency-usd", clase: "has-text-info" },
          ]
          this.cargando = false
        })
    }
  }
}
</script>
