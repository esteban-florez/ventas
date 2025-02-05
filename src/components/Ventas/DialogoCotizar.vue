<template>
	<form action="">
		<div class="modal-card" style="width: 600px">
			<header class="modal-card-head has-background-warning">
				<p class="modal-card-title">Agregar cotizacion</p>
				<button
					type="button"
					class="delete"
					@click="$emit('close', 'cotiza')"/>
			</header>
			<section class="modal-card-body">
				<p class="is-size-1 has-text-weight-bold">Total ${{ totalVenta }}</p>
				<busqueda-cliente @seleccionado="onSeleccionado"/>
				<b-field class="mt-3" label="Válido hasta">
					<b-input type="date" v-model="hasta" />
				</b-field>
			</section>
			<footer class="modal-card-foot">
				<b-button
					label="Cancelar"
					icon-left="cancel"
					size="is-medium"
					@click="$emit('close', 'cotiza')" />
				<b-button
					label="Guardar cotización"
					type="is-dark"
					icon-left="ticket-outline"
					size="is-medium"
					@click="guardarCotizacion" />
			</footer>
		</div>
	</form>
</template>
<script>
	import BusquedaCliente from '../Clientes/BusquedaCliente'

	export default{
		name:"DialogoCotizar",
		props: ['totalVenta'],
		components: { BusquedaCliente },

		data:()=>({
			cliente: {},
			hasta: null,
		}),


		methods:{
			onSeleccionado(cliente){
				this.cliente = cliente
			},


			guardarCotizacion(){
				if(Object.keys(this.cliente).length === 0) {
					this.$buefy.toast.open({
						type: 'is-danger',
						message: 'Debes seleccionar un cliente.'
					})
					return
				}

				if(!this.hasta) {
					this.$buefy.toast.open({
						type: 'is-danger',
						message: 'Debes añadir una fecha de "Válido hasta".'
					})
					return
				}

				let payload = {
					tipo: 'cotiza',
					cliente: this.cliente,
					hasta: this.hasta,
				}

				this.$emit("terminar", payload)
			}
		}
	}
</script>
