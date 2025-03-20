<template>
  <section>
    <nav-component :titulo="'Proveedores'" :texto="'Agregar proveedor'" :link="can('proveedores.registrar') ? { name: 'AgregarProveedor' } : null"/>
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

      <b-table-column field="pagar" label="Pagar" v-slot="props" v-if="can('proveedores.pagar_proveedor')">
        <b-button @click="pagar(props.row)" type="is-success" icon-left="wallet-plus" v-if="Number(props.row.deuda) > 0">
        </b-button>
      </b-table-column>

      <b-table-column field="editar" label="Editar" v-slot="props" v-if="can('proveedores.editar')">
        <b-button type="is-warning" icon-left="pen" tag="router-link" :to="{ name: 'EditarProveedor', params: { id: props.row.id } }">
        </b-button>
      </b-table-column>

      <b-table-column field="pagos" label="Pagos" v-slot="props" v-if="can('vistas.pagos')">
        <b-button type="is-primary" icon-left="cash" tag="router-link" :to="{ name: 'PagosComponent', params: { id: props.row.id } }">
        </b-button>
      </b-table-column>

      <b-table-column field="productos" label="Productos" v-slot="props" v-if="can('vistas.historial')">
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
import AyudanteSesion from '@/Servicios/AyudanteSesion'

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
    },

    pagar(proveedor) {
      this.$buefy.dialog.prompt({
        message: '¿Cual es el monto del pago que vas a registrar?',
        cancelText: 'Cancelar',
        confirmText: 'Registrar',
        inputAttrs: {
          type: 'number',
          placeholder: 'Escribe el monto del pago a registrar',
          value: '',
          min: 0.01,
          max: Number(proveedor.deuda),
          step: 0.01,
        },
        trapFocus: true,
        onConfirm: (value) => {
          this.cargando = true
          HttpService.registrar('proveedores.php', {
            accion: 'pagar_proveedor',
            pago: {
              monto: value,
              idProveedor: proveedor.id,
              idUsuario: AyudanteSesion.usuario().id,
            },
          })
            .then(registrado => {
              if (registrado) {
                this.cargando = false
                this.$buefy.toast.open('Pago registrado con éxito.')
                this.obtenerProveedores()
              }
            })

        }
      })
    },
  }
}
</script>
