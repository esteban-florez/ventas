<template>
    <form @submit.prevent="registrar">
        <b-field label="Nombre del chofer">
      <b-input step="any" icon="account" type="text" placeholder="Ej. Don Paco" v-model="datosChofer.nombre"></b-input>
    </b-field>
    <b-field label="Teléfono del chofer">
      <b-input step="any" icon="phone" type="number" placeholder="Ej. 2311459874" v-model="datosChofer.telefono"></b-input>
    </b-field>
    <b-field label="Tipo de identidad">
      <b-select class="wide" placeholder="Seleccionar..." icon="tag-multiple" v-model="datosChofer.tipo">
        <option v-for="tipo in tipos" :key="tipo" :value="tipo">
          {{ tipo }}
        </option>
      </b-select>
    </b-field>
    <b-field label="Cédula/RIF">
      <b-input type="number" placeholder="Ej. 30000000" v-model="datosChofer.ci"></b-input>
    </b-field>
    <b-field v-if="typeof datosChofer.deuda !== 'undefined'" label="Deuda actual">
      <b-input :value="formatoMonto(datosChofer.deuda)" icon="currency-usd" readonly></b-input>
    </b-field>
    <div class="buttons has-text-centered mt-3">
      <b-button type="is-primary" size="is-large" icon-left="check" native-type="submit">
        Registrar
      </b-button>
      <b-button type="is-dark" size="is-large" icon-left="cancel" @click.prevent="cancelar">
        Cancelar
      </b-button>
    </div>
    <errores-component :errores="mensajesError" v-if="mensajesError.length > 0" />
    </form>
</template>
<script>
	import Utiles from '../../Servicios/Utiles'
	import ErroresComponent from '../Extras/ErroresComponent'
  import { TIPOS_CLIENTE } from '@/consts'

	export default {
		name: "FormChofer",
		props: ["chofer", "formatoMonto"],
		components: { ErroresComponent },

		data: () => ({
			datosChofer: {
				nombre: "",
				telefono: "",
        tipo: null,
        ci: "",
			},
			mensajesError: [],
      tipos: TIPOS_CLIENTE,
		}),

		mounted() {
      if (this.chofer) {
        const copia = structuredClone(this.chofer)
        this.datosChofer = copia
      }
		},

		methods: {
			registrar() {
				this.mensajesError = Utiles.validarDatos(this.datosChofer)
				if(this.mensajesError.length > 0) return
				this.$emit("registrar", this.datosChofer)
				this.datosChofer = {
					nombre: "",
					telefono: "",
          tipo: null,
          ci: "",
				}
			},
      cancelar() {
        this.$emit("cancelar")
      }
		}
	}
</script>
