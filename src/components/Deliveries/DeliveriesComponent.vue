<template>
  <section>
    <nav-component :titulo="'Deliveries'" />
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Deliveries</b-breadcrumb-item> 
    </b-breadcrumb>
     <busqueda-en-fecha @seleccionada="onBusquedaEnFecha" />
    <div class="field mt-4">
      <div class="field has-addons">
        <div class="control is-expanded">
          <busqueda-chofer @seleccionado="onSeleccionado" @deseleccionado="onDeseleccionado" />
        </div>
      </div>
    </div>
    <mensaje-inicial class="mt-2" :titulo="'No se han encontrado deliveries :('"
      :subtitulo="'Aquí aparecerán los deliveries registrados'" v-if="deliveries.length < 1" />
    <div class="mt-2" v-if="deliveries.length > 0">
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
          <b-button type="is-primary" tag="a" :href="printHref" target="__blank" rel="noopener noreferrer">
            Imprimir
          </b-button>
        </div>
      </div>

      <div class="mb-2">
        <b-tag type="is-info" size="is-medium">
          Total de deliveries: {{ deliveries.length }}
        </b-tag>
      </div>

      <b-table class="box" :data="deliveries" :paginated="isPaginated" :per-page="perPage" :current-page.sync="currentPage"
        :pagination-simple="isPaginationSimple" :pagination-position="paginationPosition"
        :default-sort-direction="defaultSortDirection" :pagination-rounded="isPaginationRounded" :sort-icon="sortIcon"
        :sort-icon-size="sortIconSize" default-sort="user.first_name" aria-next-label="Next page"
        aria-previous-label="Previous page" aria-page-label="Page" aria-current-label="Current page">
      <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
        {{ new Date(props.row.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', }).replace(/\//g, '-') }}
      </b-table-column>

      <b-table-column field="nombreChofer" label="Nombre del chofer" sortable searchable v-slot="props">
        {{ props.row.nombreChofer }}
      </b-table-column>

      <b-table-column field="nombreCliente" label="Cliente" sortable searchable v-slot="props">
        {{ props.row.nombreCliente }}
      </b-table-column>

      <b-table-column field="costo" label="Costo" sortable searchable v-slot="props">
        ${{ formatoMonto(props.row.costo) }}
      </b-table-column>

      <b-table-column field="gratis" label="Envío gratis" sortable searchable v-slot="props">
        {{ props.row.gratis ? 'Sí' : 'No' }}
      </b-table-column>

      <b-table-column field="destino" label="Destino" sortable searchable v-slot="props">
        {{ props.row.destino }}
      </b-table-column>
    </b-table>
    </div>
    
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>

<script>
import BusquedaChofer from '../Choferes/BusquedaChofer'
import BusquedaEnFecha from '../Extras/BusquedaEnFecha'
import MensajeInicial from '../Extras/MensajeInicial'
import CartasTotales from '../Extras/CartasTotales'
import NavComponent from '../Extras/NavComponent'
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'

export default {
  name: "DeliveriesComponent",
  components: { BusquedaEnFecha, BusquedaChofer, NavComponent, MensajeInicial, CartasTotales },

  data: () => ({
    filtros: {
      fechaInicio: "",
      fechaFin: ""
    },
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
    cargando: false,
    deliveries: [],
    choferId: null,
  }),

  mounted() {
    this.obtenerDeliveries()
  },

   computed: {
    printHref() {
      let href = '#/pdf/deliveries'

      const entries = Object.entries(this.filtros)
        .filter(entry => Boolean(entry[1]))

      if (this.choferId) {
        entries.push(['choferId', this.choferId])
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

    obtenerDeliveries() {
      this.cargando = true
      let payload = {
        accion: "obtener",
        filtros: {
          ...this.filtros,
          choferId: this.choferId,
        }
      }

      HttpService.obtenerConConsultas("deliveries.php", payload)
        .then(deliveries => {
          this.deliveries = deliveries
          this.cargando = false
        })
    },

    onBusquedaEnFecha(fechas) {
      this.filtros.fechaInicio = fechas[0].toISOString().split('T')[0]
      this.filtros.fechaFin = fechas[1].toISOString().split('T')[0]
      this.obtenerDeliveries()
    },

    onDeseleccionado() {
      this.choferId = null
      this.obtenerDeliveries()
    },

    onSeleccionado(chofer) {
      this.choferId = chofer.id

      if (this.debounceTimeout) {
        clearTimeout(this.debounceTimeout);
      }

      this.debounceTimeout = setTimeout(() => {
        this.obtenerDeliveries();
      }, 500);
    },
  }
}
</script>
