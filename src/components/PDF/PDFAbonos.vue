<template>
  <section id="pdf">
    <h1>Reporte de Abonos</h1>

    <!-- Container grid with 3 per row -->
    <div class="container-grid">
      <div class="container-item">
        <div class="container-header">Monto total</div>
        <div class="container-value">${{ formatoMonto(cuentaApartado && cuentaApartado.total) }}</div>
      </div>

      <div class="container-item">
        <div class="container-header">Monto pagado</div>
        <div class="container-value">${{ formatoMonto(cuentaApartado && cuentaApartado.pagado) }}</div>
      </div>

      <div class="container-item">
        <div class="container-header">Monto por pagar</div>
        <div class="container-value">${{ formatoMonto(cuentaApartado && cuentaApartado.porPagar) }}</div>
      </div>

      <div class="container-item">
        <div class="container-header">Monto por pagar</div>
        <div class="container-value">Bs. {{ formatoMonto(cuentaApartado && cuentaApartado.porPagar * tasa) }}</div>
      </div>

      <div class="container-item">
        <div class="container-header">Nombre del cliente</div>
        <div class="container-value">{{ cuentaApartado && cuentaApartado.nombreCliente }}</div>
      </div>

      <div class="container-item">
        <div class="container-header">N° Factura</div>
        <div class="container-value">{{ cuentaApartado && cuentaApartado.id }}</div>
      </div>
    </div>

    <div v-if="abonos.length > 0">
      <b-table class="box" :data="abonos">
        <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
          {{ new Date(props.row.fecha).toLocaleDateString('es-ES', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
          }).replace(/\//g, '-') }}
        </b-table-column>

        <b-table-column field="monto" label="Monto" v-slot="props">
          ${{ formatoMonto(props.row.monto) }}
        </b-table-column>

        <b-table-column field="metodo" label="Método de pago" v-slot="props">
          {{ props.row.metodo || props.row.simple }}
        </b-table-column>

        <b-table-column field="origen" label="Origen" v-slot="props">
          {{ props.row.origen || 'N/A' }}
        </b-table-column>
      </b-table>
    </div>
    <div v-if="abonos.length < 1">
      <p>No existen abonos en el sistema.</p>
    </div>
  </section>
</template>

<script>
import Printd from 'printd'
import HttpService from '@/Servicios/HttpService'
import Utiles from '@/Servicios/Utiles'

export default {
  name: 'PDFAbonos',

  data: () => ({
    abonos: [],
    cuentaApartado: null,
    tasa: null
  }),

  methods: {
    formatoMonto (valor)
    {
      return Utiles.formatoMonto(valor)
    }
  },

  mounted ()
  {
    document.body.style.opacity = '0'

    const payload = {
      accion: 'obtener_abonos',
      id: this.$route.params.id,
    }

    HttpService.obtenerConConsultas('ventas.php', payload)
      .then(resultado =>
      {
        this.abonos = resultado.abonos
        this.cuentaApartado = resultado.cuentaApartado
        this.tasa = resultado.tasa.valor ?? 0
        return new Promise(res => setTimeout(res, 100))
      }).then(() =>
      {
        const d = new Printd()
        const table = document.querySelector('#pdf')

        const cssString = `
          .container-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
            page-break-inside: avoid;
          }
          
          .container-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
            page-break-inside: avoid;
            break-inside: avoid;
          }
          
          .container-header {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4px;
            color: #333;
          }
          
          .container-value {
            font-size: 14px;
            color: #555;
            word-break: break-word;
            overflow-wrap: break-word;
          }
          
          @media print {
            .container-grid {
              page-break-inside: avoid;
              break-inside: avoid;
            }
            
            .container-item {
              page-break-inside: avoid;
              break-inside: avoid;
            }
          }
        `

        d.onAfterPrint(() => window.close())
        d.print(table, ['/pdf.css', cssString])
      })
  },
}
</script>