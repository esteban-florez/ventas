<template>
  <section>
    <nav-component :titulo="'Historial de Inventario'" />
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Historial de Inventario</b-breadcrumb-item> 
    </b-breadcrumb>
    <b-select v-model="perPage">
        <option value="5">5 por página</option>
        <option value="10">10 por página</option>
        <option value="15">15 por página</option>
        <option value="20">20 por página</option>
      </b-select>
    <b-table class="box" :data="movimientos" :per-page="perPage" :paginated="true" :pagination-simple="false" :pagination-position="'bottom'"
    :default-sort-direction="'asc'" :pagination-rounded="true">
      <b-table-column field="nombreProducto" label="Producto" sortable searchable v-slot="props">
        {{ props.row.nombreProducto }}
      </b-table-column>

      <b-table-column field="cantidad" label="Cantidad" sortable searchable v-slot="props">
        <span class="has-text-weight-bold" :class="props.row.tipo === '+' ? 'has-text-success' : 'has-text-danger'">
          {{ props.row.tipo }}{{ props.row.cantidad }}
        </span>
      </b-table-column>
      
      <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
        {{ props.row.fecha }}
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
    perPage: 10,
  }),

  mounted() {
    this.obtenerMovimientos()
  },

  methods: {
    obtenerMovimientos() {
      this.cargando = true
      let payload = {
        accion: 'historial',
      }

      HttpService.obtenerConConsultas('ventas.php', payload)
        .then(movimientos => {
          movimientos.sort((a, b) => new Date(b.fecha).getTime() - new Date(a.fecha).getTime())
          this.movimientos = movimientos
          this.cargando = false
        })
    }
  }
}
</script>
