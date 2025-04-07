<template>
  <section id="pdf">
    <h1>Reporte de Abonos</h1>
    <div class="container">
      <p><b>Monto total:</b> {{ cuentaApartado.total }}</p>
      <p><b>Monto pagado:</b> {{ cuentaApartado.pagado }}</p>
      <p><b>Monto por pagar:</b> {{ cuentaApartado.pagado }}</p>
      <p><b>Nombre del cliente:</b> {{ cuentaApartado.nombreCliente }}</p>
    </div>
    <div v-if="abonos.length > 0">
      <b-table class="box" :data="abonos">
        <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
      {{ new Date(props.row.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', }).replace(/\//g, '-') }}
      </b-table-column>

        <b-table-column field="monto" label="Monto" v-slot="props">
          ${{ props.row.monto }}
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
import HttpService from '@/Servicios/HttpService';

export default {
  name: 'PDFAbonos',

  data: () => ({
    abonos: [],
    cuentaApartado: null,
  }),

  mounted() {
    document.body.style.opacity = '0'

    const payload = {
      accion: 'obtener_abonos',
      id: this.$route.params.id,
    }

    HttpService.obtenerConConsultas('ventas.php', payload)
      .then(resultado => {
        this.abonos = resultado.abonos
        this.cuentaApartado = resultado.cuentaApartado
        return new Promise(res => setTimeout(res, 100))
      }).then(() => {
        const d = new Printd()
        const table = document.querySelector('#pdf')

        const cssString = `
          .container {
            display: flex;
            align-items: center;
            justify-content: space-around;
          }
        `

        d.onAfterPrint(() => window.close())
        d.print(table, ['/pdf.css', cssString])
      })
  },
}
</script>
