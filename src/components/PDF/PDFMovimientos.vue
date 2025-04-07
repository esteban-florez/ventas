<template>
  <section id="pdf">
    <h1>Reporte de Movimientos</h1>
    <div v-if="movimientos.length > 0">
      <b-table class="box" :data="movimientos">
      <b-table-column field="nombreProducto" label="Producto" v-slot="props">
        {{ props.row.nombreProducto }}
      </b-table-column>

      <b-table-column field="cantidad" label="Cantidad" v-slot="props">
        <span class="has-text-weight-bold" :class="props.row.signo === '+' ? 'has-text-success' : 'has-text-danger'">
          {{ props.row.signo }}{{ props.row.cantidad }}
        </span>
      </b-table-column>

      <b-table-column field="tipo" label="Tipo" v-slot="props">
        {{ props.row.tipo }}
      </b-table-column>

      <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
      {{ new Date(props.row.fecha).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', }).replace(/\//g, '-') }}
      </b-table-column>

      <b-table-column field="nombreUsuario" label="Usuario" v-slot="props">
        {{ props.row.nombreUsuario }}
      </b-table-column>

      <b-table-column field="nombreCliente" label="Cliente" v-slot="props">
        {{ props.row.nombreCliente || 'N/A' }}
      </b-table-column>

      <b-table-column field="nombreCliente" label="Proveedor" v-slot="props">
        {{ props.row.nombreProveedor || 'N/A' }}
      </b-table-column>
      </b-table>
    </div>
    <div v-if="movimientos.length < 1">
      <p>No existen movimientos en el sistema.</p>
    </div>
  </section>
</template>

<script>
import Printd from 'printd'
import HttpService from '@/Servicios/HttpService';

export default {
  name: 'PDFMovimientos',

  data: () => ({
    movimientos: [],
  }),

  mounted() {
    document.body.style.opacity = '0'

    const payload = {
      accion: 'historial',
      proveedor: this.$route.query.proveedor || null,
    }

    HttpService.obtenerConConsultas('ventas.php', payload)
      .then(resultado => {
        resultado.sort(
          (a, b) => new Date(b.fecha).getTime() - new Date(a.fecha).getTime()
        )
        this.movimientos = resultado
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
