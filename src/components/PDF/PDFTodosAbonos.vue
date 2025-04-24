<template>
  <section id="pdf">
    <h1>Reporte de Todos los Abonos</h1>
    <div v-if="abonos.length > 0">
        <div class="has-text-centered mt-4">
        <b-button type="is-primary" icon-left="printer" @click="imprimir">
          Imprimir
        </b-button>
      </div>
      
      <b-table class="box" :data="pagosAgrupados">
        <b-table-column field="nombre" label="Método de pago" v-slot="props">
          {{ props.row.nombre }}
        </b-table-column>
        <b-table-column field="total" label="Total" v-slot="props">
          ${{ formatoMonto(props.row.total) }}
        </b-table-column>
      </b-table>

      <br />
      <br />

      <b-table class="box" :data="abonos">
        <b-table-column field="idCuenta" label="N0 factura" v-slot="props">
          {{ props.row.idCuenta }}
        </b-table-column>
        <b-table-column field="fecha" label="Fecha" v-slot="props">
          {{ new Date(props.row.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\//g, '-') }}
        </b-table-column>
        <b-table-column field="metodo" label="Método" v-slot="props">
          {{ props.row.metodo || props.row.simple }}
        </b-table-column>
        <b-table-column field="nombreCliente" label="Cliente" v-slot="props">
          {{ props.row.nombreCliente || 'MOSTRADOR' }}
        </b-table-column>
        <b-table-column field="tipo" label="Tipo" v-slot="props">
          {{ props.row.tipo }}
        </b-table-column>
        <b-table-column field="monto" label="Monto" v-slot="props">
          ${{ formatoMonto(props.row.monto) }}
        </b-table-column>
      </b-table>

      <br />
      <br />

      <div class="has-text-right" style="margin-top: 1rem;">
        <b-table
          class="box total-general-table"
          :data="abonosFiltradosTotal"
          :bordered="false"
          :striped="false"
          :narrowed="true"
          :hoverable="false"
          :pagination="false"
        >
        <b-table-column field="total" label="" v-slot="props">
          <span class="has-text-weight-bold" style="font-size:1.2em;">
            Total de abonos:
            <span class="has-text-danger">
              ${{ formatoMonto(props.row.total) }}
            </span>
          </span>
        </b-table-column>
        </b-table>
      </div>
      
    </div>
    <div v-else>
      <p>No existen abonos en el sistema.</p>
    </div>
  </section>
</template>

<script>
import HttpService from '@/Servicios/HttpService'
import Utiles from '@/Servicios/Utiles'

export default {
  name: 'PDFTodosAbonos',
  data: () => ({
    abonos: [],
    pagosAgrupados: [],
    abonosFiltradosTotal: [],
    filtrosResumen: '',
  }),
  async mounted() {
    const filtros = JSON.parse(localStorage.getItem('filtros-abonos')) || {}
    const resultado = await HttpService.obtenerConConsultas('ventas.php', {
      accion: 'obtener_todos_abonos',
      filtros,
    })
    this.abonos = resultado

    // Agrupar por método de pago
    this.pagosAgrupados = this.agruparPorMetodo(this.abonos)
    // Total general
    const total = this.abonos.reduce((acc, abono) => acc + Number(abono.monto), 0)
    this.abonosFiltradosTotal = [{ total }]
  },
  methods: {
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
    },
    agruparPorMetodo(abonos) {
      const agrupados = {}
      abonos.forEach(item => {
        const metodo = item.metodo || item.simple || 'Otro'
        if (!agrupados[metodo]) {
          agrupados[metodo] = { nombre: metodo, total: 0 }
        }
        agrupados[metodo].total += Number(item.monto)
      })
      return Object.values(agrupados)
    },
    imprimir() {
      window.print()
    }
  }
}
</script>

<style>
@media print {
  #pdf .b-table .pagination,
  #pdf .b-table .b-table-pagination,
  #pdf .has-text-centered.mt-4 {
    display: none !important;
  }
  #pdf {
    background: white !important;
    color: black !important;
  }
}

.total-general-table {
  width: 300px;
  float: right;
}
</style>