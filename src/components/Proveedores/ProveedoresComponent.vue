<template>
  <section>
    <nav-component :titulo="'Proveedores'" :texto="'Agregar proveedor'" :link="{ name: 'AgregarProveedor' }"/>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Proveedores</b-breadcrumb-item> 
    </b-breadcrumb>
    <b-table :data="proveedores">
      <b-table-column field="nombre" label="Nombre del proveedor" sortable searchable v-slot="props">
        {{ props.row.nombre }}
      </b-table-column>

      <b-table-column field="rif" label="RIF" sortable searchable v-slot="props">
        {{ props.row.rif }}
      </b-table-column>
      
      <b-table-column field="telefono" label="Teléfono" sortable searchable v-slot="props">
        {{ props.row.telefono }}
      </b-table-column>
      
      <b-table-column field="direccion" label="Dirección" sortable searchable v-slot="props">
        {{ props.row.direccion }}
      </b-table-column>

      <b-table-column field="deuda" label="Deuda" sortable searchable v-slot="props">
        ${{ props.row.deuda.toFixed(2) }}
      </b-table-column>

      <b-table-column field="editar" label="Editar" v-slot="props">
        <b-button type="is-warning" icon-left="pen" tag="router-link" :to="{ name: 'EditarProveedor', params: { id: props.row.id } }">
        </b-button>
      </b-table-column>

      <b-table-column field="pagos" label="Pagos" v-slot="props">
        <b-button type="is-success" icon-left="cash" tag="router-link" :to="{ name: 'EditarProveedor', params: { id: props.row.id } }">
        </b-button>
      </b-table-column>

      <b-table-column field="productos" label="Productos" v-slot="props">
        <b-button type="is-info" icon-left="format-list-bulleted" tag="router-link" :to="{ name: 'HistorialComponent', query: { proveedor: props.row.id } }">
        </b-button>
      </b-table-column>
    </b-table>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import NavComponent from '../Extras/NavComponent'
import HttpService from '../../Servicios/HttpService'

export default {
  name: 'ProveedoresComponent',
  components: { NavComponent },

  data: () => ({
    cargando: false,
    proveedores: []
  }),

  mounted() {
    this.obtenerProveedores()
  },

  methods: {
    obtenerProveedores() {
      this.cargando = true
      let payload = {
        accion: 'obtener',
      }

      HttpService.obtenerConConsultas('proveedores.php', payload)
        .then(proveedores => {
          this.proveedores = proveedores
          this.cargando = false
        })
    }
  }
}
</script>
