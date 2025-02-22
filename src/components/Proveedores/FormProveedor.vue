<template>
	<form>
		<b-field label="Nombre del proveedor">
      <b-input icon="account" type="text" placeholder="Ej. Empresa 123 C.A." v-model="datosProveedor.nombre"></b-input>
    </b-field>
    <b-field label="Teléfono del proveedor">
      <b-input icon="phone" type="number" placeholder="Ej. 04151231234" v-model="datosProveedor.telefono"></b-input>
    </b-field>
    <b-field label="RIF del proveedor">
      <b-input type="number" icon="card-account-details" placeholder="Ej. 304829514" v-model="datosProveedor.rif"></b-input>
    </b-field>
    <b-field label="Dirección del proveedor">
      <b-input icon="map-marker" type="text" placeholder="Ej. Edificio X, Maracay, Aragua" v-model="datosProveedor.direccion"></b-input>
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
		name: 'FormProveedor',
		props: ['proveedor'],
		components: { ErroresComponent },

		data: () => ({
			datosProveedor: {
				nombre: '',
				telefono: '',
        rif: '',
        direccion: '',
			},
			mensajesError: [],
		}),

		mounted() {
      if (this.proveedor) {
        this.datosProveedor = this.proveedor
      }
		},

		methods: {
			registrar() {
				this.mensajesError = Utiles.validarDatos(this.datosProveedor)
				if(this.mensajesError.length > 0) return
				this.$emit('registrar', this.datosProveedor)
				this.datosProveedor = {
          nombre: '',
          telefono: '',
          rif: '',
          direccion: '',
				}
			}
		}
	}
</script>
