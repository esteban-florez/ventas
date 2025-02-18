<template>
  <section>
    
    <nav-component titulo="Métodos de pago" :link="{ path: '/agregar-metodo' }" texto="Agregar método de pago" />
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item acive>Métodos de pago</b-breadcrumb-item>
    </b-breadcrumb>

    <div class="mt-2" v-if="metodos.length > 0">
      <b-select v-model="perPage">
        <option value="5">5 por página</option>
        <option value="10">10 por página</option>
        <option value="15">15 por página</option>
        <option value="20">20 por página</option>
      </b-select>

      <b-table class="box" :data="metodos" :paginated="isPaginated" :per-page="perPage" :current-page.sync="currentPage"
        :pagination-simple="isPaginationSimple" :pagination-position="paginationPosition"
        :default-sort-direction="defaultSortDirection" :pagination-rounded="isPaginationRounded" :sort-icon="sortIcon"
        :sort-icon-size="sortIconSize" aria-next-label="Next page" aria-previous-label="Previous page"
        aria-page-label="Page" aria-current-label="Current page">
        <b-table-column field="nombre" label="Nombre" sortable searchable v-slot="props">
          {{ props.row.nombre }}
        </b-table-column>

        <b-table-column field="tipo" label="Plataforma" sortable searchable v-slot="props">
          {{ props.row.tipo }}
        </b-table-column>

        <b-table-column field="acciones" label="Acciones" v-slot="props">
          <b-button type="is-danger" @click="eliminar(props.row.id)">
            <b-icon icon="delete">
            </b-icon>
          </b-button>
          <b-button tag="router-link" class="ml-4" type="is-warning" :to="{ name: 'EditarMetodo', params: { id: props.row.id } }">
            <b-icon icon="pen">
            </b-icon>
          </b-button>
        </b-table-column> 
      </b-table>
    </div>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>

<script>
import HttpService from '../../Servicios/HttpService'
import NavComponent from '../Extras/NavComponent.vue';

export default {
  name: "MetodosComponent",
  components: { NavComponent },

  data: () => ({
    cargando: false,
    metodos: [],
    isPaginated: true,
    isPaginationSimple: false,
    isPaginationRounded: true,
    paginationPosition: 'bottom',
    defaultSortDirection: 'asc',
    sortIcon: 'arrow-up',
    sortIconSize: 'is-medium',
    currentPage: 1,
    perPage: 5,
  }),

  mounted() {
    this.obtenerDatos()
  },

  methods: {
    obtenerDatos() {
      this.cargando = true
      HttpService.obtenerConConsultas('metodos.php', {
        accion: 'obtener'
      }).then(resultado => {
        this.metodos = resultado
        this.cargando = false
      })
    },

    eliminar(id) {
      this.$buefy.dialog.confirm({
        title: 'Eliminar cotización',
        message: '¿Seguro que deseas eliminar esta cotizacion?',
        confirmText: 'Sí, eliminar',
        cancelText: 'Cancelar',
        type: 'is-danger',
        hasIcon: true,
        onConfirm: () => {
          this.cargando = true
          HttpService.eliminar('metodos.php', {
            accion: 'eliminar_cotizacion',
            id: id
          }).then(resultado => {
            if (resultado) {
              this.cargando = false
              this.$buefy.toast.open('cotización eliminada')
              this.obtenerCotizaciones()
            }
          })
        }
      })
    },

    // editar(id) {
    //   this.$router.push({
    //     name: "EditarMetodo",
    //     params: { id }
    //   })
    // },
  }
}
</script>
