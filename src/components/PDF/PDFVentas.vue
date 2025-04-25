<template>
  <section id="pdf">
    <h1>Reporte de Ventas</h1>
    <div v-if="ventas.length > 0">
      <b-table class="box" :data="ventasFiltradas">
        <b-table-column field="nombre" label="Método de pago" v-slot="props">
          {{ props.row.nombre }}
        </b-table-column>
        <b-table-column field="total" label="Total pagado" v-slot="props">
          {{ props.row.total }}
        </b-table-column>
        <b-table-column field="cantidad" label="Cantidad ventas" v-slot="props">
          {{ props.row.cantidad }}
        </b-table-column>
      </b-table>
      <b-table class="box" :data="ventasFiltradasTotal">
        <b-table-column field="nombre" v-slot="props">
          {{ props.row.nombre }}
        </b-table-column>
        <b-table-column field="total" v-slot="props">
          ${{ props.row.total }}
        </b-table-column>
        <b-table-column field="cantidad" v-slot="props">
          {{ props.row.cantidad }}
        </b-table-column>
      </b-table>
      <br />
      <br />

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
import HttpService from '@/Servicios/HttpService';

export default {
  name: 'PDFVentas',

  data: () => ({
    ventas: [],
    ventasFiltradas: [],
    ventasFiltradasTotal: []
  }),

  methods: {
    async fetchSalesData() {
      this.ventasFiltradas = JSON.parse(localStorage.getItem('metodos_pago') || '[]');
      localStorage.removeItem('metodos_pago');
      this.ventasFiltradasTotal = [{
        nombre: 'Total',
        total: this.ventasFiltradas.reduce((acc, item) => acc + Number(item.total.slice(1)), 0),
        cantidad: this.ventasFiltradas.reduce((acc, item) => acc + item.cantidad, 0),
      }]
      
      const payload = {
        accion: 'obtener_ventas',
        filtros: {
          fechaInicio: this.$route.query.fechaInicio || null,
          fechaFin: this.$route.query.fechaFin || null,
          clienteId: this.$route.query.clienteId || null,
        },
      };
      
      const resultado = await HttpService.obtenerConConsultas('ventas.php', payload);
      this.ventas = resultado.ventas || [];
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