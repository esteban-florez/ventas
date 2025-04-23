<template>
  <section id="pdf">
    <h1>Reporte de Cuentas</h1>
    <div v-if="cuentas.length > 0">

      <b-table class="box" :data="pagosAgrupados">
        <b-table-column field="nombre" label="Método de pago" v-slot="props">
          {{ props.row.nombre }}
        </b-table-column>
        <b-table-column field="total" label="Total" v-slot="props">
          $ {{ props.row.total }}
        </b-table-column>
      </b-table>

      <br />
      <br />

      <b-table class="box" :data="cuentas">
        <b-table-column field="id" label="N0 Factura" v-slot="props">
          {{ props.row.id }}
        </b-table-column>
        <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
          {{ new Date(props.row.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', }).replace(/\//g, '-') }}
        </b-table-column>
        <b-table-column field="nombreCliente" label="Cliente" v-slot="props">
          {{ props.row.nombreCliente }}
        </b-table-column>
        <b-table-column field="dias" label="Duración" v-slot="props">
          {{ props.row.dias }} días
        </b-table-column>
        <b-table-column field="estado" label="Estado" v-slot="props">
          <span class="tag is-success is-large" v-if="props.row.porPagar < 1">LIQUIDADO</span>
          <span class="tag is-danger is-large" v-if="props.row.porPagar > 0">PENDIENTE</span>
        </b-table-column>
        <b-table-column field="porPagar" label="Por pagar" v-slot="props">
          <span class="has-text-danger has-text-weight-bold"> ${{ props.row.porPagar }}</span>
        </b-table-column>
      </b-table>
      
      <br />
      <br />
      
      <b-table class="box" :data="cuentasFiltradasTotal">
        <b-table-column field="totalPorPagar" label="Monto total por pagar" v-slot="props">
          ${{ props.row.totalPorPagar }}
        </b-table-column>
      </b-table>

    </div>
    <div v-else>
      <p>No existen cuentas en el sistema.</p>
    </div>
  </section>
</template>

<script>
import Printd from 'printd'
import HttpService from '@/Servicios/HttpService';

export default {
  name: 'PDFCuentas',
  components: {},

  data: () => ({
    cuentas: [],
    cuentasFiltradas: [],
    cuentasFiltradasTotal: []
  }),

  computed: {
    totalGlobalCuentas() {
      return this.cuentas.reduce((acc, cuenta) => acc + Number(cuenta.total), 0)
    },
    pagosAgrupados() {
      // Agrupa y suma los montos por método de pago
      const agrupados = {};
      this.cuentasFiltradas.forEach(item => {
        if (!agrupados[item.nombre]) {
          agrupados[item.nombre] = { nombre: item.nombre, total: 0 };
        }
        // Si el total viene como string con símbolo, lo limpiamos
        let monto = item.total;
        if (typeof monto === 'string') {
          monto = monto.replace(/[^0-9.-]+/g, '');
        }
        agrupados[item.nombre].total += Number(monto || 0);
      });
      return Object.values(agrupados);
    }
  },

  methods: {
    async fetchSalesData() {
      this.cuentasFiltradas = JSON.parse(localStorage.getItem('metodos_pago') || '[]');
      localStorage.removeItem('metodos_pago');

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

      // Sumar los porPagar de las cuentas mostradas en la tabla principal
      const totalPorPagar = this.cuentas.reduce((acc, item) => acc + Number(item.porPagar || 0), 0);
      const totalCantidad = this.cuentasFiltradas.reduce((acc, item) => acc + Number(item.cantidad || 0), 0);

      this.cuentasFiltradasTotal = [{
        nombre: 'Total',
        cantidad: totalCantidad,
        totalPorPagar: totalPorPagar
      }];
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