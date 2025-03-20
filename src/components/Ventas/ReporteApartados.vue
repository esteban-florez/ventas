<template>
  <section>
    <p class="title is-1">Reporte de apartados</p>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Apartados</b-breadcrumb-item>
    </b-breadcrumb>

    <busqueda-en-fecha @seleccionada="onBusquedaEnFecha" />
    <mensaje-inicial class="mt-2" :titulo="'No se han encontrado apartados :('"
      :subtitulo="'Aquí aparecerán los apartados registrados'" v-if="apartados.length < 1" />
    <div class="mt-2" v-if="apartados.length > 0">
      <cartas-totales :totales="totalesGenerales" />
      <tabla-cuentas-apartados :datos="apartados"
        @imprimir="onGenerarComprobante" />
    </div>
    <comprobante-compra :venta="this.apartadoSeleccionado" :tipo="'apartado'" @impreso="onImpreso"
      v-if="mostrarComprobante" :porPagar="porPagar" :tamaño="tamaño" :enviarCliente="enviarCliente" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import BusquedaEnFecha from '../Extras/BusquedaEnFecha'
import MensajeInicial from '../Extras/MensajeInicial'
import CartasTotales from '../Extras/CartasTotales'
import TablaCuentasApartados from './TablaCuentasApartados'
import ComprobanteCompra from './ComprobanteCompra'
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'

export default {
  name: "ReporteApartados",
  components: { BusquedaEnFecha, TablaCuentasApartados, MensajeInicial, CartasTotales, ComprobanteCompra },

  data: () => ({
    filtros: {
      fechaInicio: "",
      fechaFin: ""
    },
    tamaño: 'tiquera',
    porPagar: 0,
    cargando: false,
    apartados: [],
    totalesGenerales: [],
    apartadoSeleccionado: null,
    mostrarComprobante: false,
    enviarCliente: false,
  }),

  mounted() {
    this.obtenerApartados()
  },

  methods: {
    onImpreso(resultado) {
      this.mostrarComprobante = resultado
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

    async onGenerarComprobante(apartado) {
      this.apartadoSeleccionado = apartado

      const porPagar = await HttpService.obtenerConConsultas('ventas.php', {
        accion: 'por_pagar', id: apartado.id,
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
      this.obtenerApartados()
    },

    obtenerApartados() {
      this.cargando = true
      let payload = {
        filtros: this.filtros,
        accion: 'obtener_apartados'
      }
      HttpService.obtenerConConsultas('ventas.php', payload)
        .then(resultado => {
          this.apartados = resultado.apartados

          this.totalesGenerales = [
            { nombre: "# Apartados", total: this.apartados.length, icono: "wallet-travel", clase: "has-text-primary" },
            { nombre: "Total ", total: '$' + resultado.totalApartados, icono: "cash-fast", clase: "has-text-success" },
            { nombre: "Por pagar", total: '$' + resultado.totalPorPagar, icono: "alert", clase: "has-text-danger" },
            { nombre: "Pagos", total: '$' + resultado.totalPagos, icono: "account-cash", clase: "has-text-grey-light" },
            { nombre: "# Productos", total: Utiles.calcularProductosVendidos(this.apartados), icono: "package-variant", clase: "has-text-warning" },
            { nombre: "Ganancia", total: '$' + Utiles.calcularTotalGanancia(this.apartados), icono: "currency-usd", clase: "has-text-info" }
          ]
          this.cargando = false
        })
    }
  }
}
</script>
