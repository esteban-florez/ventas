<template>
  <section id="pdf">
    <h1>Reporte de Deliveries</h1>
    <div v-if="deliveries.length > 0">
      <b-table class="box" :data="deliveries">
        <b-table-column field="fecha" label="Fecha" v-slot="props">
      {{ new Date(props.row.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', }).replace(/\//g, '-') }}
      </b-table-column>

       <b-table-column field="nombreChofer" label="Nombre del chofer" v-slot="props">
        {{ props.row.nombreChofer }}
      </b-table-column>

      <b-table-column field="costo" label="Costo" v-slot="props">
        {{ props.row.costo }}
      </b-table-column>

      <b-table-column field="gratis" label="Envío gratis" v-slot="props">
        {{ props.row.gratis ? 'Sí' : 'No' }}
      </b-table-column>

      <b-table-column field="destino" label="Destino" v-slot="props">
        {{ props.row.destino }}
      </b-table-column>
      </b-table>
    </div>
    <div v-if="deliveries.length < 1">
      <p>No existen deliveries en el sistema.</p>
    </div>
  </section>
</template>

<script>
import Printd from 'printd'
import HttpService from '@/Servicios/HttpService';

export default {
  name: 'PDFDeliveries',

  data: () => ({
    deliveries: [],
    deliveriesFiltradas: [],
    deliveriesFiltradasTotal: []
  }),

  methods: {
    async fetchSalesData() {
      const payload = {
        accion: 'obtener',
        filtros: {
          fechaInicio: this.$route.query.fechaInicio || null,
          fechaFin: this.$route.query.fechaFin || null,
          choferId: this.$route.query.choferId || null,
        },
      };
      
      const resultado = await HttpService.obtenerConConsultas('deliveries.php', payload);
      this.deliveries = resultado || [];
    },
    
    printDocument() {
      return new Promise(resolve => {
        setTimeout(() => {
          const d = new Printd();
          const table = document.querySelector('#pdf');
          
          d.onAfterPrint(() => {
            window.close();
            resolve();
          });
          
          d.print(table, ['/pdf.css']);
        }, 100);
      });
    }
  },

  async mounted() {
    document.body.style.opacity = '0';
    
    await this.fetchSalesData();
    await this.printDocument();
  },
}
</script>