<template>
  <section id="pdf">
    <h1>Reporte de Cotizaciones</h1>
    <div v-if="cotizaciones.length > 0">
      <b-table class="box" :data="cotizaciones">
        <b-table-column field="fecha" label="Fecha" v-slot="props">
          {{ props.row.fecha }}
        </b-table-column>

        <b-table-column field="nombreCliente" label="Cliente" v-slot="props">
          {{ props.row.nombreCliente }}
        </b-table-column>

        <b-table-column field="nombreUsuario" label="Usuario" v-slot="props">
          {{ props.row.nombreUsuario }}
        </b-table-column>

        <b-table-column style="min-width: max-content;" field="hasta" label="Válido hasta" v-slot="props">
          {{ props.row.hasta }}
        </b-table-column>

        <b-table-column field="total" label="Total" v-slot="props">
          <b>${{ props.row.total }}</b>
        </b-table-column>

        <b-table-column field="productos" label="Productos" v-slot="props">
          <tabla-productos-vendidos :productos="props.row.productos" />
        </b-table-column>
      </b-table>
    </div>
    <div v-if="cotizaciones.length < 1">
      <p>No existen cotizaciones en el sistema.</p>
    </div>
  </section>
</template>

<script>
import Printd from 'printd'
import TablaProductosVendidos from '../Ventas/TablaProductosVendidos.vue';
import HttpService from '@/Servicios/HttpService';

export default {
  name: 'PDFCotizaciones',
  components: { TablaProductosVendidos },

  data: () => ({
    cotizaciones: [],
  }),

  mounted() {
    document.body.style.opacity = '0'

    const payload = {
      accion: 'obtener_cotizaciones',
      filtros: {
        fechaInicio: this.$route.query.fechaInicio || null,
        fechaFin: this.$route.query.fechaFin || null,
      },
    }

    HttpService.obtenerConConsultas('ventas.php', payload)
      .then(resultado => {
        this.cotizaciones = resultado.cotizaciones
        return new Promise(res => setTimeout(res, 100))
      }).then(() => {
        const d = new Printd()
        const table = document.querySelector('#pdf')

        d.onAfterPrint(() => window.close())
        d.print(table, ['/pdf.css'])
      })
  },
}
</script>
