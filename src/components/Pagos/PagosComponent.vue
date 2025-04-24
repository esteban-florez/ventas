<template>
  <section>
    <nav-component :titulo="'Pagos'" texto="Realizar pago" @click="pagar" :boton="existeDeuda && can('proveedores.pagar_proveedor')" />
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Pagos</b-breadcrumb-item>
    </b-breadcrumb>
    <h3 class="has-text-centered is-size-4 mb-3 has-text-weight-bold">
      Datos del proveedor
    </h3>
    <cartas-totales :totales="datosProveedor" :formatoMonto="formatoMonto" />
    <hr>
    <mensaje-inicial :titulo="'No se han registrado pagos'" :subtitulo="'Haz click el botón de la esquina para registrar un nuevo pago'" v-if="pagos.length < 1" />
    <template v-else>
      <h3 class="has-text-centered is-size-4 mb-3 has-text-weight-bold">
        Lista de pagos
      </h3>
      <div class="columns">
        <div class="column">
          <b-select v-model="perPage">
            <option value="5">5 por página</option>
            <option value="10">10 por página</option>
            <option value="15">15 por página</option>
            <option value="20">20 por página</option>
          </b-select>
        </div>
      </div>
      <b-table class="box" :data="pagos" :per-page="perPage" :paginated="true" :pagination-simple="false"
        :pagination-position="'bottom'" :default-sort-direction="'asc'" :pagination-rounded="true">
        <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
          {{ new Date(props.row.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', }).replace(/\//g, '-') }}
        </b-table-column>

        <b-table-column field="monto" label="Monto" sortable searchable v-slot="props">
          ${{ formatoMonto(props.row.monto) }}
        </b-table-column>

        <b-table-column field="nombreUsuario" label="Usuario" sortable searchable v-slot="props">
          {{ props.row.nombreUsuario }}
        </b-table-column>
      </b-table>
    </template>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import MensajeInicial from '../Extras/MensajeInicial'
import NavComponent from '../Extras/NavComponent'
import HttpService from '../../Servicios/HttpService'
import CartasTotales from '../Extras/CartasTotales.vue';
import AyudanteSesion from '@/Servicios/AyudanteSesion';
import Utiles from '../../Servicios/Utiles'

export default {
  name: "PagosComponent",
  components: { MensajeInicial, NavComponent, CartasTotales },

  data: () => ({
    cargando: false,
    pagos: [],
    perPage: 5,
    proveedor: null,
    datosProveedor: [],
  }),

  async mounted() {
    await this.obtenerPagos()
  },

  methods: {
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
    },
    async obtenerPagos() {
      this.cargando = true
      let payload = {
        accion: 'obtener_pagos',
        id: this.$route.params.id,
      }

      const datos = await HttpService.obtenerConConsultas('proveedores.php', payload)
      const { pagos, proveedor } = datos

      this.pagos = pagos
      this.proveedor = proveedor
      this.datosProveedor = [
        {
          nombre: 'Nombre del proveedor',
          total: proveedor.nombre,
          icono: 'account',
          clase: 'has-text-primary',
        },
        {
          nombre: 'Monto total',
          total: proveedor.total,
          icono: 'cash',
          clase: 'has-text-info',
        },
        {
          nombre: 'Monto pagado',
          total: proveedor.pagado,
          icono: 'check-circle',
          clase: 'has-text-success',
        },
        {
          nombre: 'Monto por pagar',
          total: proveedor.deuda,
          icono: 'clock',
          clase: 'has-text-danger',
        },
      ]

      this.cargando = false
    },

    pagar() {
      this.$buefy.dialog.prompt({
        message: '¿Cual es el monto del pago que vas a registrar?',
        cancelText: 'Cancelar',
        confirmText: 'Registrar',
        inputAttrs: {
          type: 'number',
          placeholder: 'Escribe el monto del pago a registrar',
          value: '',
          min: 0.01,
          max: Number(this.proveedor.deuda),
          step: 0.01,
        },
        trapFocus: true,
        onConfirm: (value) => {
          this.cargando = true
          HttpService.registrar('proveedores.php', {
            accion: 'pagar_proveedor',
            pago: {
              monto: value,
              idProveedor: this.proveedor.id,
              idUsuario: AyudanteSesion.usuario().id,
            },
          })
            .then(registrado => {
              if (registrado) {
                this.cargando = false
                this.$buefy.toast.open('Pago registrado con éxito.')
                this.obtenerPagos()
              }
            })

        }
      })
    },
  },

  computed: {
    existeDeuda() {
      return this.proveedor && Number(this.proveedor.deuda) > 0
    }
  },
}
</script>
