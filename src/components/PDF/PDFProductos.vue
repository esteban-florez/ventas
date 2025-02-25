<template>
  <section id="pdf">
    <h1>Reporte de Productos</h1>
    <div v-if="productos.length > 0">
      <b-table class="box" :data="productos">
        <b-table-column field="codigo" label="Codigo" v-slot="props">
          {{ props.row.codigo }}
        </b-table-column>

        <b-table-column field="nombre" label="Nombre" v-slot="props">
          {{ props.row.nombre }}
        </b-table-column>

        <b-table-column field="precioCompra" label="Precio compra" v-slot="props">
          ${{ props.row.precioCompra }}
        </b-table-column>

        <b-table-column field="precioVenta" label="Precio venta" v-slot="props">
          ${{ props.row.precioVenta }}
        </b-table-column>

        <b-table-column field="precioVenta2" label="Precio venta 2" v-slot="props">
          ${{ props.row.precioVenta2 }}
        </b-table-column>

        <b-table-column field="precioVenta3" label="Precio venta 3" v-slot="props">
          ${{ props.row.precioVenta3 }}
        </b-table-column>

        <b-table-column field="ganancia" label="Ganacia" v-slot="props">
          <b>${{ props.row.precioVenta - props.row.precioCompra }}</b>
        </b-table-column>

        <b-table-column field="vendidoMayoreo" label="¿Mayoreo?" v-slot="props">
          <b-tag type="is-danger" v-if="!props.row.vendidoMayoreo">No</b-tag>

          <div v-if="props.row.vendidoMayoreo">
            <b>Precio: </b>${{ props.row.precioMayoreo }}<br>
            <b>A partir: </b>{{ props.row.cantidadMayoreo }}
          </div>
        </b-table-column>

        <b-table-column field="existencia" label="Existencia" v-slot="props">
          {{ props.row.existencia }} {{ props.row.unidad }}
        </b-table-column>

        <b-table-column field="nombreMarca" label="Marca" v-slot="props">
          {{ props.row.nombreMarca }}
        </b-table-column>

        <b-table-column field="nombreCategoria" label="Categoría" v-slot="props">
          {{ props.row.nombreCategoria }}
        </b-table-column>

        <b-table-column field="nombreProveedor" label="Proveedor" v-slot="props">
          {{ props.row.nombreProveedor }}
        </b-table-column>
      </b-table>
    </div>
    <div v-if="productos.length < 1">
      <p>No existen productos en el sistema.</p>
    </div>
  </section>
</template>

<script>
import Printd from 'printd'
import HttpService from '@/Servicios/HttpService';

export default {
  name: 'PDFProductos',

  data: () => ({
    productos: [],
  }),

  mounted() {
    document.body.style.opacity = '0'
    const accion = 'obtener'

    HttpService.obtenerConConsultas('productos.php', { accion })
      .then(resultado => {
        this.productos = resultado.productos
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
