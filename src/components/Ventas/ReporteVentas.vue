<template>
  <section>
    <p class="title is-1">Reporte de ventas</p>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Ventas</b-breadcrumb-item>
    </b-breadcrumb>
    <busqueda-en-fecha @seleccionada="onBusquedaEnFecha" />
    <div class="field mt-4">
      <div class="field has-addons">
        <div class="control is-expanded">
          <busqueda-cliente @seleccionado="onSeleccionado" @deseleccionado="onDeseleccionado" />
        </div>
      </div>
    </div>
    <b-modal
    v-model="abrirModalLocal" has-modal-card trap-focus :destroy-on-hide="false" aria-role="dialog"
    aria-label="Modal Seleccionar Local" close-button-aria-label="Close" aria-modal>
      <div class="modal-card" style="width: 320px">
        <section class="modal-card-body">
          <b-field class="mt-3" label="Local emisor">
            <b-select class="wide" placeholder="Seleccionar..." v-model="local" required>
              <option value="jiro">Jirosushi Prime</option>
              <option value="ccs">Oferta Caracas</option>
              <option value="prime">Food Prime</option>
            </b-select>
          </b-field>
        </section>
        <footer class="modal-card-foot" style="justify-content: flex-end">
          <b-button label="Continuar" type="is-primary" @click="confirmarLocal" :disabled="!local" />
        </footer>
      </div>
    </b-modal>
    <mensaje-inicial class="mt-2" :titulo="'No se han encontrado ventas :('"
      :subtitulo="'Aquí aparecerán las ventas registradas'" v-if="ventas.length < 1" />
    <div class="mt-2" v-if="ventas.length > 0">
      <cartas-totales :totales="totalesGenerales" />
      <cartas-totales-filtradas :metodosPago="ventasFiltradas" />
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
          <b-button type="is-primary" tag="a" :href="printHref" @click="guardarVentasFiltradas" target="__blank" rel="noopener noreferrer">
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
      {{ new Date(props.row.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', }).replace(/\//g, '-') }}
      </b-table-column>

        <b-table-column field="nombreCliente" label="Cliente" sortable searchable v-slot="props">
          {{ props.row.nombreCliente }}
        </b-table-column>

        <b-table-column field="pagado" label="Pago" sortable v-slot="props">
          ${{ formatoMonto(props.row.pagado) }}
        </b-table-column>

        <b-table-column field="Cambio" label="Cambio" sortable v-slot="props">
          ${{ formatoMonto(props.row.pagado - props.row.total) }}
        </b-table-column>

        <b-table-column field="total" label="Total" sortable v-slot="props">
          <b>${{ formatoMonto(props.row.total) }}</b> 
        </b-table-column>

        <b-table-column field="metodo" label="Método de pago" sortable v-slot="props">
          {{ props.row.simple || props.row.nombreMetodo }}
        </b-table-column>

        <b-table-column field="origen" label="Origen" sortable v-slot="props">
          {{ props.row.origen || 'N/A' }}
        </b-table-column>

        <b-table-column field="ticket" label="Comprobante" v-slot="props">
          <b-button type="is-info" icon-left="ticket-outline"
            @click="generarComprobante(props.row)">Comprobante</b-button>
        </b-table-column>

        <b-table-column field="acciones" label="Acciones" v-slot="props">
          <b-button type="is-warning" icon-left="pencil-outline" tag="router-link"
          :to="{ name: 'EditarVenta', params: { id: props.row.id } }" v-if="can('ventas.editar')">Editar</b-button>
          <b-button type="is-danger" icon-left="trash-can-outline" 
            @click="eliminarVenta(props.row)" v-if="can('ventas.eliminar')">Eliminar</b-button>
        </b-table-column>

      </b-table>
    </div>
    <comprobante-compra :venta="this.ventaSeleccionada" :tipo="'venta'" v-if="mostrarComprobante"
      @impreso="onImpreso" :tamaño="tamaño" :enviarCliente="enviarCliente" :local="local" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>

<script>
import BusquedaCliente from '../Clientes/BusquedaCliente'
import BusquedaEnFecha from '../Extras/BusquedaEnFecha'
import MensajeInicial from '../Extras/MensajeInicial'
import CartasTotales from '../Extras/CartasTotales'
import CartasTotalesFiltradas from '../Extras/CartasTotalesFiltradas'
import ComprobanteCompra from './ComprobanteCompra'
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'

export default {
  name: "ReporteVentas",
  components: { BusquedaEnFecha, MensajeInicial, CartasTotales, CartasTotalesFiltradas, ComprobanteCompra, BusquedaCliente },

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
    ventaSeleccionada: null,
    enviarCliente: false,
    clienteId: null,
    ventasFiltradas: [],
    local: null,
    abrirModalLocal: false,
  }),

  mounted() {
    this.obtenerVentas()
  },

  computed: {
    printHref() {
      let href = '#/pdf/ventas'

      const entries = Object.entries(this.filtros)
        .filter(entry => Boolean(entry[1]))

      if (this.clienteId) {
        entries.push(['clienteId', this.clienteId])
      }

      if (entries.length === 0) return href

      const filtros = Object.fromEntries(entries)
      const params = new URLSearchParams(filtros).toString()
      return `${href}?${params}`
    },
  },

  methods: {
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
    },

    onImpreso(resultado) {
      this.mostrarComprobante = resultado
    },

    generarComprobante(venta) {
      this.ventaSeleccionada = venta
      this.local = null

      this.$buefy.dialog.confirm({
        message: 'Selecciona el tamaño a imprimir',
        cancelText: 'Carta',
        confirmText: 'Tiquera',
        trapFocus: true,
        onConfirm: () => {
          this.tamaño = 'tiquera'
          this.abrirModalLocal = true
        },
        onCancel: () => {
          this.tamaño = 'carta'
          this.abrirModalLocal = true
        },
      })
    },

    async guardarEdicionVenta() {
      this.cargando = true
      try {
        const respuesta = await HttpService.obtenerConConsultas('ventas.php', {
          accion: 'editar_venta',
          id: this.ventaEditando.id,
          venta: this.ventaEditando
        })
        if (respuesta.success) {
          this.$buefy.toast.open({
            message: 'Venta editada correctamente',
            type: 'is-success'
          })
          this.obtenerVentas()
          this.mostrarModalEditar = false
        } else {
          this.$buefy.toast.open({
            message: respuesta.message || 'No se pudo editar la venta',
            type: 'is-danger'
          })
        }
      } catch (e) {
        this.$buefy.toast.open({
          message: 'Error al editar la venta',
          type: 'is-danger'
        })
      } finally {
        this.cargando = false
      }
    },

    async eliminarVenta(venta) {
      this.$buefy.dialog.confirm({
        message: '¿Estás seguro de que deseas eliminar esta venta?',
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
        type: 'is-danger',
        onConfirm: async () => {
          this.cargando = true
          try {
            const respuesta = await HttpService.obtenerConConsultas('ventas.php', {
              accion: 'eliminar_venta',
              id: venta.id
            })
            if (respuesta || respuesta.success) {
              this.$buefy.toast.open({
                message: 'Venta eliminada correctamente',
                type: 'is-success'
              })
              this.obtenerVentas()
            } else {
              this.$buefy.toast.open({
                message: respuesta.message || 'No se pudo eliminar la venta',
                type: 'is-danger'
              })
            }
          } catch (e) {
            this.$buefy.toast.open({
              message: 'Error al eliminar la venta',
              type: 'is-danger'
            })
          } finally {
            this.cargando = false
          }
        }
      })
    },

    confirmarLocal() {
      this.abrirModalLocal = false;
      this.confirmarEnvioCliente();
    },

    confirmarEnvioCliente() {
      this.abrirModalLocal = false
      this.$buefy.dialog.confirm({
        message: '¿Enviar al cliente mediante WhatsApp?',
        cancelText: 'No',
        confirmText: 'Sí',
        trapFocus: true,
        onConfirm: () => {
          this.enviarCliente = true
          this.mostrarComprobante = true
        },
        onCancel: () => {
          this.enviarCliente = false
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
      let payload = {
        filtros: {
          fechaInicio: this.filtros.fechaInicio || null,
          fechaFin: this.filtros.fechaFin || null,
          clienteId: this.clienteId || null
        },
        accion: 'obtener_ventas'
      }

      HttpService.obtenerConConsultas('ventas.php', payload)
        .then(resultado => {
          this.ventas = resultado.ventas
          this.ventasFiltradas = resultado.ventasFiltradas.map(venta => {
            return {
              nombre: venta.metodo_pago,
              total: `$ ${this.formatoMonto(venta.total_pagado)}`,
              icono: 'credit-card-outline',
              clase: 'has-text-info',
              cantidad: venta.ventas_totales,
            }
          })

          this.totalesGenerales = [
            { nombre: "No. Ventas", total: this.ventas.length, icono: "cart", clase: "has-text-primary" },
            { nombre: "Total ventas", total: '$' + this.formatoMonto(resultado.totalVentas), icono: "cash-fast", clase: "has-text-success" },
            { nombre: "Ganancia", total: '$' + this.formatoMonto(Utiles.calcularTotalGanancia(this.ventas)), icono: "currency-usd", clase: "has-text-info" },
            { nombre: "Productos vendidos", total: Utiles.calcularProductosVendidos(this.ventas), icono: "package-variant", clase: "has-text-danger" },
          ]
          this.cargando = false
        })
    },

    guardarVentasFiltradas() {
      const ventasFiltradas = this.ventasFiltradas.map(venta => {
        return {
          nombre: venta.nombre,
          total: venta.total,
          cantidad: venta.cantidad,
        }
      })
      localStorage.setItem('metodos_pago', JSON.stringify(ventasFiltradas));
    },

    onDeseleccionado() {
      this.clienteId = null
      this.obtenerVentas()
    },

    onSeleccionado(cliente) {
      this.clienteId = cliente.id

      if (this.debounceTimeout) {
        clearTimeout(this.debounceTimeout);
      }

      this.debounceTimeout = setTimeout(() => {
        this.obtenerVentas();
      }, 500);
    },
  }
}
</script>

