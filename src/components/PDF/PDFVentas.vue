<template>
  <section id="pdf">
    <h1>Reporte de Ventas</h1>
    <div v-if="ventas.length > 0">
      <b-table class="box" :data="ventas">
        <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
      {{ new Date(props.row.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', }).replace(/\//g, '-') }}
      </b-table-column>

        <b-table-column field="nombreCliente" label="Cliente" v-slot="props">
          {{ props.row.nombreCliente }}
        </b-table-column>

        <b-table-column field="nombreUsuario" label="Usuario" v-slot="props">
          {{ props.row.nombreUsuario }}
        </b-table-column>

        <b-table-column field="pagado" label="Pago" v-slot="props">
          ${{ props.row.pagado }}
        </b-table-column>

        <b-table-column field="Cambio" label="Cambio" v-slot="props">
          ${{ props.row.pagado - props.row.total }}
        </b-table-column>

        <b-table-column field="total" label="Total" v-slot="props">
          <b>${{ props.row.total }}</b>
        </b-table-column>

        <b-table-column field="metodo" label="Método de pago" v-slot="props">
          {{ props.row.simple || props.row.nombreMetodo }}
        </b-table-column>

        <b-table-column field="origen" label="Origen" v-slot="props">
          {{ props.row.origen || 'N/A' }}
        </b-table-column>

        <b-table-column field="productos" label="Productos" v-slot="props">
          <tabla-productos-vendidos :productos="props.row.productos" />
        </b-table-column>
      </b-table>
    </div>
    <div v-if="ventas.length < 1">
      <p>No existen ventas en el sistema.</p>
    </div>
  </section>
</template>

<script>
import Printd from 'printd'
import TablaProductosVendidos from '../Ventas/TablaProductosVendidos.vue';
import HttpService from '@/Servicios/HttpService';

export default {
  name: 'PDFVentas',
  components: { TablaProductosVendidos },

  data: () => ({
    ventas: [],
  }),

  mounted() {
    document.body.style.opacity = '0'

    const payload = {
      accion: 'obtener_ventas',
      filtros: {
        fechaInicio: this.$route.query.fechaInicio || null,
        fechaFin: this.$route.query.fechaFin || null,
      },
    }

    HttpService.obtenerConConsultas('ventas.php', payload)
      .then(resultado => {
        this.ventas = resultado.ventas
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
