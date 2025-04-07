<template>
  <section id="pdf">
    <h1>Reporte de Cuentas</h1>
    <div v-if="cuentas.length > 0">
      <b-table class="box" :data="cuentas">
        <b-table-column field="fecha" label="Fecha" v-slot="props">
          {{ props.row.fecha }}
        </b-table-column>

        <b-table-column field="nombreCliente" label="Cliente" v-slot="props">
          {{ props.row.nombreCliente }}
        </b-table-column>

        <b-table-column field="nombreUsuario" label="Usuario" v-slot="props">
          {{ props.row.nombreUsuario }}
        </b-table-column>

        <b-table-column field="pagado" label="Pago" v-slot="props">
          <span class="has-text-info has-text-weight-bold">${{ props.row.pagado }}</span>
        </b-table-column>

        <b-table-column field="porPagar" label="Por pagar" v-slot="props">
          <span class="has-text-danger has-text-weight-bold"> ${{ props.row.porPagar }}</span>
        </b-table-column>

        <b-table-column field="total" label="Total" v-slot="props">
          <b>${{ props.row.total }}</b>
        </b-table-column>

        <b-table-column field="dias" label="Duración" v-slot="props">
          {{ props.row.dias }} días
        </b-table-column>

        <b-table-column field="productos" label="Productos" v-slot="props">
          <tabla-productos-vendidos :productos="props.row.productos" />
        </b-table-column>

        <b-table-column field="estado" label="Estado" v-slot="props">
          <span class="tag is-success is-large" v-if="props.row.porPagar < 1">LIQUIDADO</span>
          <span class="tag is-danger is-large" v-if="props.row.porPagar > 0">PENDIENTE</span>
        </b-table-column>
      </b-table>
    </div>
    <div v-if="cuentas.length < 1">
      <p>No existen cuentas en el sistema.</p>
    </div>
  </section>
</template>

<script>
import Printd from 'printd'
import TablaProductosVendidos from '../Ventas/TablaProductosVendidos.vue';
import HttpService from '@/Servicios/HttpService';

export default {
  name: 'PDFCuentas',
  components: { TablaProductosVendidos },

  data: () => ({
    cuentas: [],
  }),

  mounted() {
    document.body.style.opacity = '0'

    const payload = { 
      accion: 'obtener_cuentas',
      filtros: {
        fechaInicio: this.$route.query.fechaInicio || null,
        fechaFin: this.$route.query.fechaFin || null,
      },
    }

    HttpService.obtenerConConsultas('ventas.php', payload)
      .then(resultado => {
        this.cuentas = resultado.cuentas
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
