<template>
	<form>
		<b-field label="Nombre del cliente">
      <b-input step="any" icon="account" type="text" placeholder="Ej. Don Paco" v-model="datosCliente.nombre"></b-input>
    </b-field>
    <b-field label="Teléfono del cliente">
      <b-input step="any" icon="phone" type="number" placeholder="Ej. 2311459874" v-model="datosCliente.telefono"></b-input>
    </b-field>
    <b-field label="Tipo de cliente">
      <b-select class="wide" placeholder="Tipo de cliente" icon="tag-multiple" v-model="datosCliente.tipo">
        <option v-for="tipo in tipos" :key="tipo" :value="tipo">
          {{ tipo }}
        </option>
      </b-select>
    </b-field>
    <b-field label="Cédula/RIF">
      <b-input type="number" placeholder="Ej. 30000000" v-model="datosCliente.ci"></b-input>
    </b-field>
    <div class="buttons has-text-centered mt-3">
      <b-button type="is-primary" size="is-large" icon-left="check" @click="registrar">Registrar</b-button>
      <b-button type="is-dark" size="is-large" icon-left="cancel" tag="router-link" to="/clientes">Cancelar</b-button>
    </div>
    <errores-component :errores="mensajesError" v-if="mensajesError.length > 0" />
	</form>
</template>
<script>
	import Utiles from '../../Servicios/Utiles'
	import ErroresComponent from '../Extras/ErroresComponent'
  import { TIPOS_CLIENTE } from '@/consts'

	export default {
		name: "FormCliente",
		props: ["cliente"],
		components: { ErroresComponent },

		data:()=>({
			datosCliente: {
				nombre: "",
				telefono: "",
        tipo: "",
        ci: "",
			},
			mensajesError: [],
      tipos: TIPOS_CLIENTE,
		}),

		mounted(){
			this.datosCliente = this.cliente
		},

		methods: {
			registrar(){
				this.mensajesError = Utiles.validarDatos(this.datosCliente)
				if(this.mensajesError.length > 0) return
				this.$emit("registrar", this.datosCliente)
				this.datosCliente  = {
					nombre: "",
					telefono: "",
          tipo: "",
          ci: "",
				}
			}
		}
	}
</script>
