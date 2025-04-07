<template>
  <section>
    <nav-component :titulo="'Historial de Inventario'" />
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Historial de Inventario</b-breadcrumb-item>
    </b-breadcrumb>
    <div class="columns">
      <div class="column is-flex">
        <b-select class="mr-1" v-model="perPage">
          <option value="5">5 por página</option>
          <option value="10">10 por página</option>
          <option value="15">15 por página</option>
          <option value="20">20 por página</option>
        </b-select>
        <b-select class="has-text-black" v-model="filtros.proveedor" @input="obtenerMovimientos">
          <option value="">Todos los proveedores</option>
          <option v-for="proveedor in proveedores" :value="proveedor.id" :key="proveedor.id">
            {{ proveedor.nombre }}
          </option>
        </b-select>
      </div>
      <div class="column is-flex is-justify-content-end">
        <b-button type="is-primary" tag="a" :href="printHref" target="__blank" rel="noopener noreferrer">
          Imprimir
        </b-button>
      </div>
    </div>
    <b-table class="box" :data="movimientos" :per-page="perPage" :paginated="true" :pagination-simple="false"
      :pagination-position="'bottom'" :default-sort-direction="'asc'" :pagination-rounded="true">
      <b-table-column field="nombreProducto" label="Producto" sortable searchable v-slot="props">
        {{ props.row.nombreProducto }}
      </b-table-column>

      <b-table-column field="cantidad" label="Cantidad" sortable searchable v-slot="props">
        <span class="has-text-weight-bold" :class="props.row.signo === '+' ? 'has-text-success' : 'has-text-danger'">
          {{ props.row.signo }}{{ props.row.cantidad }}
        </span>
      </b-table-column>

      <b-table-column field="tipo" label="Tipo" sortable searchable v-slot="props">
        {{ props.row.tipo }}
      </b-table-column>

      <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
        {{ props.row.fecha }}
      </b-table-column>

      <b-table-column field="nombreUsuario" label="Usuario" sortable searchable v-slot="props">
        {{ props.row.nombreUsuario }}
      </b-table-column>

      <b-table-column field="nombreCliente" label="Cliente" sortable searchable v-slot="props">
        {{ props.row.nombreCliente || 'N/A' }}
      </b-table-column>

      <b-table-column field="nombreCliente" label="Proveedor" sortable searchable v-slot="props">
        {{ props.row.nombreProveedor || 'N/A' }}
      </b-table-column>
    </b-table>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import NavComponent from '../Extras/NavComponent'
import HttpService from '../../Servicios/HttpService'

export default {
  name: 'HistorialComponent',
  components: { NavComponent },

  data: () => ({
    cargando: false,
    movimientos: [],
    proveedores: [],
    filtros: {
      proveedor: '',
    },
    perPage: 10,
  }),

  mounted() {
    this.filtros.proveedor = this.$route.query.proveedor || ''
    this.obtenerMovimientos()
    this.obtenerProveedores()
  },

  computed: {
    printHref() {
      let href = '#/pdf/movimientos'

      const entries = Object.entries(this.filtros)
        .filter(entry => Boolean(entry[1]))

      if (entries.length === 0) return href

      const filtros = Object.fromEntries(entries)
      const params = new URLSearchParams(filtros).toString()
      return `${href}?${params}`
    },
  },

  methods: {
    obtenerMovimientos() {
      this.cargando = true
      let payload = {
        accion: 'historial',
        proveedor: this.filtros.proveedor || null,
      }

      HttpService.obtenerConConsultas('ventas.php', payload)
        .then(movimientos => {
          movimientos.sort((a, b) => new Date(b.fecha).getTime() - new Date(a.fecha).getTime())
          this.movimientos = movimientos
          this.cargando = false
        })
    },

    obtenerProveedores() {
      this.cargando = true
      let payload = {
        accion: 'obtener',
      }

      HttpService.obtenerConConsultas('proveedores.php', payload)
        .then(proveedores => {
          this.proveedores = proveedores
          this.cargando = false
        })
    },
  },
}
</script>
