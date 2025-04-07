<template>
  <section>
    <p class="title is-1">Reporte de cuentas</p>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Cuentas</b-breadcrumb-item>
    </b-breadcrumb>
    <busqueda-en-fecha @seleccionada="onBusquedaEnFecha" />
    <mensaje-inicial class="mt-2" :titulo="'No se han encontrado cuentas :('"
      :subtitulo="'Aquí aparecerán las cuentas registradas'" v-if="cuentas.length < 1" />
    <div class="mt-2" v-if="cuentas.length > 0">
      <cartas-totales :totales="totalesGenerales" />
      <tabla-cuentas-apartados :datos="cuentas"
        @imprimir="onGenerarComprobante" :printHref="printHref" />
    </div>
    <comprobante-compra :venta="this.cuentaSeleccionada" :tipo="'cuenta'" @impreso="onImpreso" v-if="mostrarComprobante"
      :porPagar="porPagar" :tamaño="tamaño" :enviarCliente="enviarCliente" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import BusquedaEnFecha from '../Extras/BusquedaEnFecha'
import MensajeInicial from '../Extras/MensajeInicial'
import CartasTotales from '../Extras/CartasTotales'
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'
import TablaCuentasApartados from './TablaCuentasApartados'
import ComprobanteCompra from './ComprobanteCompra'

export default {
  name: "ReporteCuentas",
  components: { BusquedaEnFecha, TablaCuentasApartados, MensajeInicial, CartasTotales, ComprobanteCompra },

  data: () => ({
    filtros: {
      fechaInicio: "",
      fechaFin: ""
    },
    tamaño: 'tiquera',
    cargando: false,
    cuentas: [],
    totalesGenerales: [],
    cuentaSeleccionada: null,
    mostrarComprobante: false,
    porPagar: 0,
    enviarCliente: false,
  }),

  mounted() {
    this.obtenerCuentas()
  },

  computed: {
    printHref() {
      let href = '#/pdf/cuentas'

      const entries = Object.entries(this.filtros)
        .filter(entry => Boolean(entry[1]))

      if (entries.length === 0) return href

      const filtros = Object.fromEntries(entries)
      const params = new URLSearchParams(filtros).toString()
      return `${href}?${params}`
    },
  },

  methods: {
    onImpreso(resultado) {
      this.mostrarComprobante = resultado
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
          this.confirmarEnvioCliente()
        },
        onCancel: () => {
          this.tamaño = 'carta'
          this.confirmarEnvioCliente()
        },
      })
    },

    confirmarEnvioCliente() {
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

    onBusquedaEnFecha(fechas) {
      this.filtros.fechaInicio = fechas[0].toISOString().split('T')[0]
      this.filtros.fechaFin = fechas[1].toISOString().split('T')[0]
      this.obtenerCuentas()
    },

    obtenerCuentas() {
      this.cargando = true
      let payload = {
        filtros: this.filtros,
        accion: 'obtener_cuentas'
      }
      HttpService.obtenerConConsultas('ventas.php', payload)
        .then(resultado => {
          this.cuentas = resultado.cuentas

          this.totalesGenerales = [
            { nombre: "# Cuentas", total: this.cuentas.length, icono: "wallet", clase: "has-text-primary" },
            { nombre: "Total ", total: '$' + resultado.totalCuentas, icono: "cash-fast", clase: "has-text-success" },
            { nombre: "Por pagar", total: '$' + resultado.totalPorPagar, icono: "alert", clase: "has-text-danger" },
            { nombre: "Pagos", total: '$' + resultado.totalPagos, icono: "account-cash", clase: "has-text-grey-light" },
            { nombre: "# Productos", total: Utiles.calcularProductosVendidos(this.cuentas), icono: "package-variant", clase: "has-text-warning" },
            { nombre: "Ganancia", total: '$' + Utiles.calcularTotalGanancia(this.cuentas), icono: "currency-usd", clase: "has-text-info" }
          ]
          this.cargando = false
        })
    }
  }
}
</script>
