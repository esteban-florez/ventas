<template>
  <section id="pdf">
    <h1>Reporte de Clientes</h1>
    <div v-if="clientes.length > 0">
      <b-table class="box" :data="clientes">
        <b-table-column field="nombre" label="Nombre del cliente" v-slot="props">
          {{ props.row.nombre }}
        </b-table-column>

        <b-table-column field="ci" label="Cédula/RIF" v-slot="props">
          {{ props.row.ci }}
        </b-table-column>

        <b-table-column field="tipo" label="Tipo" v-slot="props">
          {{ props.row.tipo }}
        </b-table-column>

        <b-table-column field="telefono" label="Teléfono" v-slot="props">
          {{ props.row.telefono }}
        </b-table-column>
      </b-table>
    </div>
    <div v-if="clientes.length < 1">
      <p>No existen clientes en el sistema.</p>
    </div>
  </section>
</template>

<script>
import Printd from 'printd'
import HttpService from '@/Servicios/HttpService';

export default {
  name: 'PDFClientes',

  data: () => ({
    clientes: [],
  }),

  mounted() {
    document.body.style.opacity = '0'
    const accion = 'obtener'

    HttpService.obtenerConConsultas('clientes.php', { accion })
      .then(resultado => {
        this.clientes = resultado
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
