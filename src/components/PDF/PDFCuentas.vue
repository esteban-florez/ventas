<template>
  <section id="pdf">
    <h1>Reporte de Cuentas</h1>
    <div v-if="cuentas.length > 0">
      <b-table class="box" :data="cuentasFiltradas">
        <b-table-column field="nombre" label="Método de pago" v-slot="props">
          {{ props.row.nombre }}
        </b-table-column>
        <b-table-column field="total" label="Total pagado" v-slot="props">
          {{ props.row.total }}
        </b-table-column>
        <b-table-column field="cantidad" label="Cantidad cuentas" v-slot="props">
          {{ props.row.cantidad }}
        </b-table-column>
      </b-table>
      <b-table class="box" :data="cuentasFiltradasTotal">
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

      <b-table class="box" :data="cuentas">
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
    cuentasFiltradas: [],
    cuentasFiltradasTotal: []
  }),

  methods: {
    async fetchSalesData() {
      this.cuentasFiltradas = JSON.parse(localStorage.getItem('metodos_pago') || '[]');
      localStorage.removeItem('metodos_pago');
      this.cuentasFiltradasTotal = [{
        nombre: 'Total',
        total: this.cuentasFiltradas.reduce((acc, item) => acc + Number(item.total.slice(1)), 0),
        cantidad: this.cuentasFiltradas.reduce((acc, item) => acc + item.cantidad, 0),
      }]
      
      const payload = {
        accion: 'obtener_cuentas',
        filtros: {
          fechaInicio: this.$route.query.fechaInicio || null,
          fechaFin: this.$route.query.fechaFin || null,
          clienteId: this.$route.query.clienteId || null,
        },
      };
      
      const resultado = await HttpService.obtenerConConsultas('ventas.php', payload);
      this.cuentas = resultado.cuentas || [];
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
