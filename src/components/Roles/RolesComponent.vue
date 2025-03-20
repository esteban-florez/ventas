<template>
  <section>
    <nav-component :titulo="'Roles'" :texto="'Agregar rol'" :link="can('roles.registrar') ? { name: 'AgregarRol' }: null"/>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Roles</b-breadcrumb-item> 
    </b-breadcrumb>
    <div class="columns">
        <div class="column">
          <b-select v-model="perPage">
            <option value="5">5 por página</option>
            <option value="10">10 por página</option>
            <option value="15">15 por página</option>
            <option value="20">20 por página</option>
          </b-select>
        </div>
      </div>
    <b-table class="box" :data="roles" :per-page="perPage" :paginated="true" :pagination-simple="false"
    :pagination-position="'bottom'" :default-sort-direction="'asc'" :pagination-rounded="true">
      <b-table-column field="nombre" label="Nombre del rol" sortable searchable v-slot="props">
        {{ props.row.nombre }}
      </b-table-column>

      <b-table-column field="usuarios" label="Cantidad de usuarios" sortable searchable v-slot="props">
        {{ props.row.numUsuarios }} usuarios con este rol
      </b-table-column>

      <b-table-column field="editar" label="Editar" v-slot="props" v-if="can('roles.editar')">
        <b-button type="is-warning" icon-left="pen" tag="router-link" :to="{ name: 'EditarRol', params: { id: props.row.id } }" v-if="props.row.nombre !== 'Administrador'">
          Editar
        </b-button>
        <span v-else>NO EDITABLE</span>
      </b-table-column>
    </b-table>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import NavComponent from '../Extras/NavComponent'
import HttpService from '../../Servicios/HttpService'

export default {
  name: 'RolesComponent',
  components: { NavComponent },

  data: () => ({
    cargando: false,
    roles: [],
    perPage: 5,
  }),

  mounted() {
    this.obtenerRoles()
  },

  methods: {
    obtenerRoles() {
      this.cargando = true
      let payload = {
        accion: 'obtener',
      }

      HttpService.obtenerConConsultas('roles.php', payload)
        .then(roles => {
          this.roles = roles
          this.cargando = false
        })
    },
  }
}
</script>
