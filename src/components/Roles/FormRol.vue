<template>
	<form @submit.prevent="registrar">
		<b-field label="Nombre del rol">
      <b-input icon="account" type="text" placeholder="Ej. Empresa 123 C.A." v-model="nombre" minlength="4" maxlength="50" required></b-input>
    </b-field>
    <b-field label="Permisos del rol">
      <div class="permisos-grid">
        <div v-for="(_, permiso) in permisos" :key="permiso">
          <b-checkbox v-model="permisos[permiso]">
            {{ PERMISOS[permiso] }}
          </b-checkbox>
        </div>
      </div>
    </b-field>
    <div class="buttons has-text-centered mt-3">
      <b-button type="is-primary" icon-left="check" native-type="submit">Aceptar</b-button>
      <b-button type="is-dark" icon-left="cancel" tag="router-link" to="/roles">Cancelar</b-button>
    </div>
    <errores-component :errores="mensajesError" v-if="mensajesError.length > 0" />
	</form>
</template>
<script>
	import Vue from 'vue'
  import ErroresComponent from '../Extras/ErroresComponent'
  import { PERMISOS } from '@/consts'

	export default {
		name: 'FormRol',
		props: ['rol'],
		components: { ErroresComponent },

		data: () => ({
      nombre: '',
      permisos: {},
			mensajesError: [],
      PERMISOS: {},
		}),

		mounted() {
      this.PERMISOS = PERMISOS

      if (this.rol) {
        this.nombre = this.rol.nombre
        Object.entries(this.rol.permisos).forEach(([key, value]) => {
          Vue.set(this.permisos, key, value)
        })
      } else {
        Object.keys(PERMISOS).forEach(permiso => {
          Vue.set(this.permisos, permiso, true)
        })
      }
		},

		methods: {
			registrar() {
				this.$emit('registrar', { nombre: this.nombre, permisos: JSON.stringify(this.permisos) })

				this.nombre = ''
        this.permisos = {}
			}
		},
	}
</script>

<style scoped>
.permisos-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
}
</style>
