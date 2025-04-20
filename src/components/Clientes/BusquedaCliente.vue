<template>
	<section>
		<b-field label="Nombre del cliente">
			<b-autocomplete
				v-model="cliente"
				id="cliente"
				placeholder="Escribe el nombre del cliente"
				:keep-first="true"
				:data="clientesFiltrados"
				field="nombre"
				@input="buscarClientes"
				@select="seleccionarCliente"
			>
			</b-autocomplete>
		</b-field>
		<div class="notification is-info mt-2" v-if="clienteSeleccionado">
			<button class="delete" @click="deseleccionarCliente"></button>
			<p>Cliente: <b>{{ clienteSeleccionado.nombre }}</b></p>
			<p>Teléfono: <b>{{ clienteSeleccionado.telefono }}</b></p>
		</div>
	</section>
</template>
<script>
	import HttpService from '../../Servicios/HttpService'

	export default{
		name: "BusquedaCliente",
		props: {
			initialCliente: {
				type: Object,
				default: () => null
			}
		},

		data: () => ({
			cliente: "",
			clientesEncontrados: [],
			clienteSeleccionado: null
		}),

		methods: {
			deseleccionarCliente(){
				this.clienteSeleccionado = null
				this.$emit('deseleccionado')
			},
			seleccionarCliente(opcion) {
				if(!opcion) return
				this.clienteSeleccionado = opcion
				this.$emit("seleccionado", this.clienteSeleccionado)
				setTimeout(() => this.cliente = '', 10)
			},

			buscarClientes(){
				let payload = {
					accion: 'obtener_por_nombre',
					nombre: this.cliente
				}

				HttpService.obtenerConConsultas('clientes.php', payload)
				.then(clientes =>{ 
					this.clientesEncontrados = clientes
				})
			},
		},

		watch: {
			initialCliente: {
				immediate: true,
				handler(newVal) {
					if (newVal && newVal.nombre) {
						this.clienteSeleccionado = newVal;
						this.cliente = newVal.nombre;
					}
				}
			}
		},

		computed: {
			clientesFiltrados() {
				return this.clientesEncontrados.filter(opcion => {
					return (
						opcion.nombre
							.toString()
							.toLowerCase()
							.indexOf(this.cliente.toLowerCase()) >= 0
					)
				})
			}
		}

	}
</script>
