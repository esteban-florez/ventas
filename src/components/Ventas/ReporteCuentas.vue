<template>
  <section>
    <p class="title is-1">Reporte de cuentas</p>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Cuentas</b-breadcrumb-item>
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
    <mensaje-inicial class="mt-2" :titulo="'No se han encontrado cuentas :('"
      :subtitulo="'Aquí aparecerán las cuentas registradas'" v-if="cuentas.length < 1" />
    <div class="mt-2" v-if="cuentas.length > 0">
      <cartas-totales :totales="totalesGenerales" :formatoMonto="formatoMonto" />
      <cartas-totales-filtradas :metodosPago="cuentasFiltradas" :formatoMonto="formatoMonto" />
      <tabla-cuentas-apartados
        :datos="cuentas"
        :printHref="printHref"
        :formatoMonto="formatoMonto"
        :filtros="filtrosCompletos"
        @imprimir="onGenerarComprobante"
      />
    </div>
    <comprobante-compra :venta="this.cuentaSeleccionada" :tipo="'cuenta'" @impreso="onImpreso" v-if="mostrarComprobante"
      :porPagar="porPagar" :tamaño="tamaño" :enviarCliente="enviarCliente" :local="local" />
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>

<script>
import BusquedaCliente from '../Clientes/BusquedaCliente'
import BusquedaEnFecha from '../Extras/BusquedaEnFecha'
import MensajeInicial from '../Extras/MensajeInicial'
import CartasTotales from '../Extras/CartasTotales'
import CartasTotalesFiltradas from '../Extras/CartasTotalesFiltradas'
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'
import TablaCuentasApartados from './TablaCuentasApartados'
import ComprobanteCompra from './ComprobanteCompra'

export default {
  name: "ReporteCuentas",
  components: { BusquedaEnFecha, TablaCuentasApartados, MensajeInicial, CartasTotales, CartasTotalesFiltradas, ComprobanteCompra, BusquedaCliente },

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
    clienteId: null,
    cuentasFiltradas: [],
    local: null,
    abrirModalLocal: false,
  }),

  mounted() {
    this.obtenerCuentas()
  },

  computed: {
    printHref() {
      let href = '#/pdf/cuentas'

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
    filtrosCompletos() {
      return {
        ...this.filtros,
        clienteId: this.clienteId || null,
        clienteNombre: this.clienteSeleccionado ? this.clienteSeleccionado.nombre : null
      }
    }
  },

  methods: {
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
    },

    onImpreso(resultado) {
      this.mostrarComprobante = resultado
    },

    async onGenerarComprobante(cuenta) {
      this.cuentaSeleccionada = cuenta
      const porPagar = await HttpService.obtenerConConsultas('ventas.php', {
        accion: 'por_pagar', id: cuenta.id,
      })
      this.porPagar = porPagar

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

    confirmarLocal() {
      this.abrirModalLocal = false;
      this.confirmarEnvioCliente();
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
      this.filtros = {
        ...this.filtros,
        clienteId: this.clienteId || null
      }

      let payload = {
        filtros: this.filtros,
        accion: 'obtener_cuentas'
      }
      HttpService.obtenerConConsultas('ventas.php', payload)
        .then(resultado => {
          this.cuentas = resultado.cuentas
          this.cuentasFiltradas = resultado.cuentasFiltradas.map(cuenta => {
            return {
              nombre: cuenta.metodo_pago,
              total: Number(cuenta.total_pagado), // Guardar como número
              icono: 'credit-card-outline',
              clase: 'has-text-info',
              cantidad: cuenta.cuentas_apartados_totales,
            }
          })

          this.totalesGenerales = [
            { nombre: "# Cuentas", total: this.cuentas.length, icono: "wallet", clase: "has-text-primary" },
            { nombre: "Total ", total: this.formatoMonto(resultado.totalCuentas), icono: "cash-fast", clase: "has-text-success" },
            { nombre: "Por pagar", total: this.formatoMonto(resultado.totalPorPagar), icono: "alert", clase: "has-text-danger" },
            { nombre: "Pagos", total: this.formatoMonto(resultado.totalPagos), icono: "account-cash", clase: "has-text-grey-light" },
            { nombre: "# Productos", total: Utiles.calcularProductosVendidos(this.cuentas), icono: "package-variant", clase: "has-text-warning" },
            { nombre: "Ganancia", total: this.formatoMonto(Utiles.calcularTotalGanancia(this.cuentas)), icono: "currency-usd", clase: "has-text-info" }
          ]
          this.cargando = false
        })
    },

    guardarCuentasApartadosFiltrados() {
      const cuentasFiltradas = this.cuentasFiltradas.map(cuenta => {
        return {
          nombre: cuenta.nombre,
          total: cuenta.total,
          cantidad: cuenta.cantidad,
        }
      })
      localStorage.setItem('metodos_pago', JSON.stringify(cuentasFiltradas));
    },

    onDeseleccionado() {
      this.clienteId = null
      this.obtenerCuentas()
    },

    onSeleccionado(cliente) {
      this.clienteId = cliente.id

      if (this.debounceTimeout) {
        clearTimeout(this.debounceTimeout);
      }

      this.debounceTimeout = setTimeout(() => {
        this.obtenerCuentas();
      }, 500);
    },
  }
}
</script>
