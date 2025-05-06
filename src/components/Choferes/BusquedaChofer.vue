<template>
  <section>
    <b-field label="Nombre del chofer">
      <b-autocomplete
        v-model="chofer"
        id="chofer"
        placeholder="Escribe el nombre del chofer"
        :keep-first="true"
        :data="choferesFiltrados"
        field="nombre"
        @input="buscarChoferes"
        @select="seleccionarChofer"
      >
        <template slot-scope="props">
          <div>
            <span>{{ props.option.nombre }}</span>
          </div>
        </template>
      </b-autocomplete>
    </b-field>
    <div class="notification is-info mt-2" v-if="choferSeleccionado">
      <button class="delete" @click="deseleccionarChofer"></button>
      <p>Chofer: <b>{{ choferSeleccionado.nombre }}</b></p>
      <p>Teléfono: <b>{{ choferSeleccionado.telefono }}</b></p>
    </div>
  </section>
</template>
<script>
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'

export default{
  name: "BusquedaChofer",
  props: {
    initialChofer: {
      type: Object,
      default: () => null
    }
  },

  data: () => ({
    chofer: "",
    choferesEncontrados: [],
    choferSeleccionado: null
  }),

  methods: {
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
    },
    deseleccionarChofer(){
      this.choferSeleccionado = null
      this.$emit('deseleccionado')
    },
    seleccionarChofer(opcion) {
      if(!opcion) return
      this.choferSeleccionado = opcion
      this.$emit("seleccionado", this.choferSeleccionado)
      setTimeout(() => this.chofer = '', 10)
    },

    buscarChoferes(){
      let payload = {
        accion: 'obtener_por_nombre',
        nombre: this.chofer
      }

      HttpService.obtenerConConsultas('choferes.php', payload)
      .then(choferes =>{ 
        this.choferesEncontrados = choferes
      })
    },
  },

  watch: {
    initialChofer: {
      immediate: true,
      handler(newVal) {
        if (newVal && newVal.nombre) {
          this.choferSeleccionado = newVal;
          this.chofer = newVal.nombre;
        }
      }
    }
  },

  computed: {
    choferesFiltrados() {
      return this.choferesEncontrados.filter(opcion => {
        return (
          opcion.nombre
            .toString()
            .toLowerCase()
            .indexOf(this.chofer.toLowerCase()) >= 0
        )
      })
    }
  }

}
</script>
