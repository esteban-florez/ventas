<template>
  <section>
    <nav-component :titulo="'Deliveries'" />
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Deliveries</b-breadcrumb-item> 
    </b-breadcrumb>
    <b-table :data="deliveries">
      <b-table-column field="nombreChofer" label="Nombre del chofer" sortable searchable v-slot="props">
        {{ props.row.nombreChofer }}
      </b-table-column>

      <b-table-column field="costo" label="Costo" sortable searchable v-slot="props">
        {{ props.row.costo }}
      </b-table-column>

      <b-table-column field="gratis" label="Envío gratis" sortable searchable v-slot="props">
        {{ props.row.gratis ? 'Sí' : 'No' }}
      </b-table-column>

      <b-table-column field="destino" label="Destino" sortable searchable v-slot="props">
        {{ props.row.destino }}
      </b-table-column>
    </b-table>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import NavComponent from '../Extras/NavComponent'
import HttpService from '../../Servicios/HttpService'

export default {
  name: "DeliveriesComponent",
  components: { NavComponent },

  data: () => ({
    cargando: false,
    deliveries: []
  }),

  mounted() {
    this.obtenerDeliveries()
  },

  methods: {
    obtenerDeliveries() {
      this.cargando = true
      let payload = {
        accion: "obtener",
      }

      HttpService.obtenerConConsultas("deliveries.php", payload)
        .then(deliveries => {
          this.deliveries = deliveries
          this.cargando = false
        })
    }
  }
}
</script>
