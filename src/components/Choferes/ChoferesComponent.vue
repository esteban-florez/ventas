<template>
  <section>
    <nav-component :titulo="'Choferes'" />
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Choferes</b-breadcrumb-item> 
    </b-breadcrumb>

    <!-- Botón para registrar nuevo chofer -->
    <div class="mb-3">
      <b-button type="is-primary" icon-left="plus" @click="mostrarNuevoChofer = true">
        Registrar nuevo chofer
      </b-button>
    </div>

    <!-- Modal para el formulario de nuevo chofer -->
    <b-modal v-model="mostrarNuevoChofer" has-modal-card trap-focus :destroy-on-hide="true">
      <div class="modal-card">
        <header class="modal-card-head">
          <p class="modal-card-title">Registrar nuevo chofer</p>
          <button type="button" class="delete" @click="mostrarNuevoChofer = false"></button>
        </header>
        <section class="modal-card-body">
          <form-chofer @registrar="registrarChofer" @cancelar="mostrarNuevoChofer = false" :formatoMonto="formatoMonto" />
        </section>
      </div>
    </b-modal>

    <div class="columns">
      <div class="column">
        <b-select v-model="perPage">
          <option value="5">5 por página</option>
          <option value="10">10 por página</option>
          <option value="15">15 por página</option>
          <option value="20">20 por página</option>
        </b-select>
      </div>
      <div class="column is-flex is-justify-content-end">
        <b-button type="is-primary" tag="a" href="#/pdf/choferes" target="__blank" rel="noopener noreferrer">
          Imprimir
        </b-button>
      </div>
    </div>
    
    <b-table class="box" :data="choferes" :per-page="perPage" :paginated="true" :pagination-simple="false"
    :pagination-position="'bottom'" :default-sort-direction="'asc'" :pagination-rounded="true">
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
        ${{ formatoMonto(props.row.deuda) }}
      </b-table-column>

      <b-table-column field="acciones" label="Acciones" v-slot="props">
        <div class="is-flex">
          <b-button type="is-info" icon-left="pen" tag="router-link" :to="{ name: 'EditarChofer', params: { id: props.row.id } }" v-if="can('choferes.editar')">
            Editar
          </b-button>
          <b-button v-if="tieneDeuda(props.row) && can('choferes.pagar_chofer')" class="ml-1" type="is-success" icon-left="plus" @click="pagar(props.row)">
            Pagar
          </b-button>
        </div>
      </b-table-column>
    </b-table>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import NavComponent from '../Extras/NavComponent'
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'
import FormChofer from './FormChofer.vue'

export default {
  name: "ChoferesComponent",
  components: { NavComponent, FormChofer },

  data: () => ({
    cargando: false,
    perPage: 5,
    choferes: [],
    mostrarNuevoChofer: false, // Nuevo estado para mostrar el modal
  }),

  mounted() {
    this.obtenerChoferes()
  },

  methods: {
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
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
    },

    tieneDeuda(chofer) {
      return Number(chofer.deuda) > 0;
    },

    pagar(chofer) {
      this.$buefy.dialog.prompt({
        message: '¿Cual es el monto del pago que vas a registrar?',
        cancelText: 'Cancelar',
        confirmText: 'Registrar',
        inputAttrs: {
          type: 'number',
          placeholder: 'Escribe el monto del pago a registrar',
          value: '',
          min: 0.01,
          max: Number(chofer.deuda),
          step: 0.01,
        },
        trapFocus: true,
        onConfirm: (value) => {
          this.cargando = true
          HttpService.registrar('choferes.php', {
            accion: 'pagar_chofer',
            pago: {
              monto: value,
              idChofer: chofer.id,
            },
          })
            .then(registrado => {
              if (registrado) {
                this.cargando = false
                this.$buefy.toast.open('Pago registrado con éxito.')
                this.obtenerChoferes()
              }
            })

        }
      })
    },

    registrarChofer(datosChofer) {
      this.cargando = true
      this.mostrarNuevoChofer = false
      this.$buefy.toast.open('Registrando chofer...')
      HttpService.registrar('choferes.php', {
        accion: 'registrar',
        chofer: datosChofer
      }).then(() => {
        this.obtenerChoferes()
        this.$buefy.toast.open('Chofer registrado con éxito.')
        this.cargando = false
      }).catch(() => {
        this.$buefy.toast.open({
          message: 'Error al registrar chofer.',
          type: 'is-danger'
        })
        this.cargando = false
      })
    }
  },
}
</script>
