<template>
	<form>
		<b-field label="Monto del pago">
      <b-input icon="cash-fast" type="number" placeholder="Ej. 2000." v-model="datosPago.monto"></b-input>
    </b-field>
    <b-field label="Número de factura">
      <b-input type="number" icon="receipt" placeholder="Ej. 304829514" v-model="datosPago.factura"></b-input>
    </b-field>
    <div class="buttons has-text-centered mt-3">
      <b-button type="is-primary" icon-left="check" @click="registrar">Registrar</b-button>
      <b-button type="is-dark" icon-left="cancel" tag="router-link" to="/proveedores">Cancelar</b-button>
    </div>
    <errores-component :errores="mensajesError" v-if="mensajesError.length > 0" />
	</form>
</template>
<script>
	import Utiles from '../../Servicios/Utiles'
	import ErroresComponent from '../Extras/ErroresComponent'

	export default {
		name: 'FormPago',
		components: { ErroresComponent },

		data: () => ({
      datosPago: {
        monto: '',
        factura: '',
      },
			mensajesError: [],
		}),

		methods: {
			registrar() {
				this.mensajesError = Utiles.validarDatos(this.datosPago)
				if(this.mensajesError.length > 0) return
				this.$emit('registrar', this.datosPago)
			}
		}
	}
</script>
