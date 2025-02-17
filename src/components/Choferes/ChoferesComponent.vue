<template>
  <section>
    <nav-component :titulo="'Choferes'" />
    <b-table :data="choferes">
      <b-table-column field="nombre" label="Nombre del chofer" sortable searchable v-slot="props">
        {{ props.row.nombre }}
      </b-table-column>

      <b-table-column field="ci" label="Cédula/RIF" sortable searchable v-slot="props">
        {{ props.row.ci }}
      </b-table-column>
      
      <b-table-column field="tipo" label="Tipo" sortable searchable v-slot="props">
        {{ props.row.tipo }}
      </b-table-column>

      <b-table-column field="telefono" label="Teléfono" sortable searchable v-slot="props">
        {{ props.row.telefono }}
      </b-table-column>

      <b-table-column field="deuda" label="Deuda Total" sortable searchable v-slot="props">
        ${{ Number(props.row.deuda).toFixed(2) || 0..toFixed(2) }}
      </b-table-column>

      <b-table-column field="eliminar" label="Eliminar" v-slot="props">
        <b-button type="is-danger" icon-left="delete" @click="eliminar(props.row.id)">Eliminar</b-button>
      </b-table-column>

      <b-table-column field="editar" label="Editar" v-slot="props">
        <b-button type="is-info" icon-left="pen" tag="router-link" :to="{ name: 'EditarChofer', params: { id: props.row.id } }">
          Editar
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
  name: "ChoferesComponent",
  components: { NavComponent },

  data: () => ({
    cargando: false,
    choferes: []
  }),

  mounted() {
    this.obtenerChoferes()
  },

  methods: {
    async eliminar(idChofer) {
      this.$buefy.dialog.confirm({
        title: 'Eliminar chofer',
        message: 'Seguro que quieres <b>eliminar</b> este chofer? Esta acción no se puede revertir.',
        confirmText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        type: 'is-danger',
        hasIcon: true,
        onConfirm: () => this.confirmarEliminar(idChofer),
      })
    },

    async confirmarEliminar(idChofer) {
      this.cargando = true
      const resultado = HttpService.eliminar('choferes.php', {
        accion: 'eliminar',
        id: idChofer
      })

      if (!resultado) {
        this.$buefy.toast.open('Error al eliminar')
        this.cargando = false
        return
      }

      if (resultado) {
        this.cargando = false
        this.$buefy.toast.open({
          type: 'is-info',
          message: 'Chofer eliminado.'
        })
        this.obtenerChoferes()
      }
    },

    obtenerChoferes() {
      this.cargando = true
      let payload = {
        accion: "obtener",
      }

      HttpService.obtenerConConsultas("choferes.php", payload)
        .then(choferes => {
          this.choferes = choferes
          this.cargando = false
        })
    }
  }
}
</script>
