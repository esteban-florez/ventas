<template>
  <section>
    <p class="title is-1">Reporte de ventas</p>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Ventas</b-breadcrumb-item>
    </b-breadcrumb>
    <busqueda-en-fecha @seleccionada="onBusquedaEnFecha" />
    <mensaje-inicial class="mt-2" :titulo="'No se han encontrado ventas :('"
      :subtitulo="'Aquí aparecerán las ventas registradas'" v-if="ventas.length < 1" />
    <div class="mt-2" v-if="ventas.length > 0">
      <cartas-totales :totales="totalesGenerales" />
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
          <b-button type="is-primary" tag="a" href="#/pdf/ventas" target="__blank" rel="noopener noreferrer">
            Imprimir
          </b-button>
        </div>
      </div>

      <b-table class="box" :data="ventas" :paginated="isPaginated" :per-page="perPage" :current-page.sync="currentPage"
        :pagination-simple="isPaginationSimple" :pagination-position="paginationPosition"
        :default-sort-direction="defaultSortDirection" :pagination-rounded="isPaginationRounded" :sort-icon="sortIcon"
        :sort-icon-size="sortIconSize" default-sort="user.first_name" aria-next-label="Next page"
        aria-previous-label="Previous page" aria-page-label="Page" aria-current-label="Current page">
        <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
          {{ props.row.fecha }}
        </b-table-column>

        <b-table-column field="nombreCliente" label="Cliente" sortable searchable v-slot="props">
          {{ props.row.nombreCliente }}
        </b-table-column>

        <b-table-column field="nombreUsuario" label="Usuario" sortable searchable v-slot="props">
          {{ props.row.nombreUsuario }}
        </b-table-column>

        <b-table-column field="pagado" label="Pago" sortable v-slot="props">
          ${{ props.row.pagado }}
        </b-table-column>

        <b-table-column field="Cambio" label="Cambio" sortable v-slot="props">
          ${{ props.row.pagado - props.row.total }}
        </b-table-column>

        <b-table-column field="total" label="Total" sortable v-slot="props">
          <b>${{ props.row.total }}</b>
        </b-table-column>

        <b-table-column field="metodo" label="Método de pago" sortable v-slot="props">
          {{ props.row.simple || props.row.nombreMetodo }}
        </b-table-column>

        <b-table-column field="origen" label="Origen" sortable v-slot="props">
          {{ props.row.origen || 'N/A' }}
        </b-table-column>

        <b-table-column field="productos" label="Productos" sortable v-slot="props">
          <tabla-productos-vendidos :productos="props.row.productos" />
        </b-table-column>

        <b-table-column field="ticket" label="Comprobante" v-slot="props">
          <b-button type="is-info" icon-left="ticket-outline"
            @click="generarComprobante(props.row)">Comprobante</b-button>
        </b-table-column>
      </b-table>
    </div>
    <comprobante-compra :venta="this.ventaSeleccionada" :tipo="'venta'" v-if="mostrarComprobante"
      @impreso="onImpreso" :tamaño="tamaño" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import BusquedaEnFecha from '../Extras/BusquedaEnFecha'
import MensajeInicial from '../Extras/MensajeInicial'
import CartasTotales from '../Extras/CartasTotales'
import TablaProductosVendidos from './TablaProductosVendidos'
import ComprobanteCompra from './ComprobanteCompra'
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'

export default {
  name: "ReporteVentas",
  components: { BusquedaEnFecha, TablaProductosVendidos, MensajeInicial, CartasTotales, ComprobanteCompra },

  data: () => ({
    filtros: {
      fechaInicio: "",
      fechaFin: ""
    },
    tamaño: 'tiquera',
    cargando: false,
    ventas: [],
    isPaginated: true,
    isPaginationSimple: false,
    isPaginationRounded: true,
    paginationPosition: 'bottom',
    defaultSortDirection: 'asc',
    sortIcon: 'arrow-up',
    sortIconSize: 'is-medium',
    currentPage: 1,
    perPage: 5,
    totalesGenerales: [],
    mostrarComprobante: false,
    ventaSeleccionada: null
  }),

  mounted() {
    this.obtenerVentas()
  },

  methods: {
    onImpreso(resultado) {
      this.mostrarComprobante = resultado
    },

    generarComprobante(venta) {
      this.ventaSeleccionada = venta

      this.$buefy.dialog.confirm({
        message: 'Selecciona el tamaño a imprimir',
        cancelText: 'Carta',
        confirmText: 'Tiquera',
        trapFocus: true,
        onConfirm: () => {
          this.tamaño = 'tiquera'
          this.mostrarComprobante = true
        },
        onCancel: () => {
          this.tamaño = 'carta'
          this.mostrarComprobante = true
        },
      })
    },

    async onGenerarComprobante(cuenta) {
      this.cuentaSeleccionada = cuenta
      const porPagar = await HttpService.obtenerConConsultas('ventas.php', {
        accion: 'por_pagar', id: cuenta.id,
      })

      this.porPagar = porPagar

      this.$buefy.dialog.confirm({
        message: 'Selecciona el tamaño a imprimir',
        cancelText: 'Carta',
        confirmText: 'Tiquera',
        trapFocus: true,
        onConfirm: () => {
          this.tamaño = 'tiquera'
          this.mostrarComprobante = true
        },
        onCancel: () => {
          this.tamaño = 'carta'
          this.mostrarComprobante = true
        },
      })
    },

    onBusquedaEnFecha(fechas) {
      this.filtros.fechaInicio = fechas[0].toISOString().split('T')[0]
      this.filtros.fechaFin = fechas[1].toISOString().split('T')[0]
      this.obtenerVentas()
    },

    obtenerVentas() {
      this.cargando = true
      console.log(this.filtros)
      let payload = {
        filtros: {
          fechaInicio: this.filtros.fechaInicio || null,
          fechaFin: this.filtros.fechaFin || null,
        },
        accion: 'obtener_ventas'
      }

      HttpService.obtenerConConsultas('ventas.php', payload)
        .then(resultado => {
          this.ventas = resultado.ventas

          this.totalesGenerales = [
            { nombre: "No. Ventas", total: this.ventas.length, icono: "cart", clase: "has-text-primary" },
            { nombre: "Total ventas", total: '$' + resultado.totalVentas, icono: "cash-fast", clase: "has-text-success" },
            { nombre: "Ganancia", total: '$' + Utiles.calcularTotalGanancia(this.ventas), icono: "currency-usd", clase: "has-text-info" },
            { nombre: "Productos vendidos", total: Utiles.calcularProductosVendidos(this.ventas), icono: "package-variant", clase: "has-text-danger" },
          ]
          this.cargando = false
        })
    }
  }
}
</script>
