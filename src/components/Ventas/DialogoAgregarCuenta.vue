<template>
	<form action="">
		<div class="modal-card" style="width: 600px">
			<header class="modal-card-head has-background-info">
				<p class="modal-card-title has-text-white">Agregar a cuenta</p>
				<button
					type="button"
					class="delete"
					@click="$emit('close', 'cuenta')"/>
			</header>
			<section class="modal-card-body">
				<p class="is-size-1 has-text-weight-bold">Total ${{ totalVenta }}</p>
				<busqueda-cliente @seleccionado="onSeleccionado"/>
				<b-field label="Pago inicial">
					<b-input step="any" icon="currency-usd" type="number" placeholder="Cuánto deja el cliente" v-model="pagado" @input="calcularRestante" size="is-medium"></b-input>
				</b-field>
				<b-field label="Vence en">
          <b-select class="wide" icon="tag-multiple" v-model="dias">
            <option value="" disabled>Seleccionar...</option>
            <option v-for="dia in DIAS" :key="dia" :value="dia">
              {{ dia }} días
            </option>
          </b-select>
				</b-field>
				<p class="is-size-1 has-text-weight-bold">Por Pagar ${{ porPagar }}</p>
			</section>
			<footer class="modal-card-foot">
				<b-button
					label="Cancelar"
					icon-left="cancel"
					size="is-medium"
					@click="$emit('close', 'cuenta')" />
				<b-button
					label="Agregar a cuenta"
					type="is-info"
					icon-left="wallet-plus"
					size="is-medium"
					@click="agregarCuenta" />
			</footer>
		</div>
	</form>
</template>
<script>
	import BusquedaCliente from '../Clientes/BusquedaCliente'
  import { DIAS } from '@/consts'

	export default{
		name:"DialogoAgregarCuenta",
		props: ['totalVenta'],
		components: { BusquedaCliente },

		data:()=>({
			pagado: "",
      dias: "",
			porPagar: 0,
			cliente: {},
      DIAS: DIAS,
		}),


		mounted(){
				this.calcularRestante()
			},

		methods:{
			onSeleccionado(cliente){
				this.cliente = cliente
			},


			calcularRestante(){
				this.porPagar = parseFloat(this.totalVenta-this.pagado)
			},

			agregarCuenta(){
				if (Object.keys(this.cliente).length === 0) {
					this.$buefy.toast.open({
            type: 'is-danger',
            message: 'Debes seleccionar un cliente.'
          })
          return
				}

        if (!this.dias) {
          this.$buefy.toast.open({
            type: 'is-danger',
            message: 'Debes seleccionar los días de vencimiento.'
          })
        }

				let payload = {
					tipo: 'cuenta',
					pagado: this.pagado,
					porPagar: this.porPagar,
					cliente: this.cliente,
          dias: this.dias,
				}

				this.$emit("terminar", payload)
			}	
		
		}
	}
</script>
