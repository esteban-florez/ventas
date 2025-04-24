<template>
	<b-table
		:data="listaProductos">
			<b-table-column field="codigo" label="Código" v-slot="props">
				{{ props.row.codigo }}
			</b-table-column>

			<b-table-column field="nombre" label="Nombre" v-slot="props">
				{{ props.row.nombre }}
			</b-table-column>

			<b-table-column field="precio" label="Precio" v-slot="props">
        <span v-if="props.row.mayoreoAplicado">
          ${{ formatoMonto(props.row.precio) }}
        </span>
        <b-select class="wide" icon="tag-multiple" v-model="props.row.precio" required @input="$emit('precioCambiado')" v-if="!props.row.mayoreoAplicado">
						<option v-for="precio in precios(props.row)" :key="precio" :value="precio">
						{{ formatoMonto(precio) }}
						</option>
        </b-select>
			</b-table-column>

			<b-table-column field="cantidad" label="Cantidad" v-slot="props">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <b-field class="mb-0">
            <b-numberinput @input="aumentar(props.row)" min="0.1" step="0.01" :max="props.row.existencia" v-model="props.row.cantidad" style="width: 13em">
            </b-numberinput>
          </b-field>
          <span>
            {{ unidades[props.row.unidad].toLowerCase() }}
          </span>
        </div>
			</b-table-column>

			<b-table-column field="subtotal" label="Subtotal" v-slot="props">
<<<<<<< HEAD
				<b>${{ formatoMonto(props.row.precio * props.row.cantidad) }}</b>
=======
				<b>${{ (props.row.precio * props.row.cantidad).toFixed(2) }}</b>
>>>>>>> 70e0b202801913e5e151590033cb4a8fbbcf18f3
			</b-table-column>

			<b-table-column field="quitar" label="Quitar" v-slot="props">
				<b-button icon-left="delete" type="is-danger" @click="quitar(props.row.id)">
					Quitar
				</b-button>
			</b-table-column>
		</b-table>
</template>
<script>
import { UNIDADES } from '@/consts';
import Utiles from '../../Servicios/Utiles'

	export default {
		name: "TablaProductos",
		props: ["listaProductos"],

		data:()=>({
      unidades: UNIDADES
		}),

		methods: {
			quitar(id) {
				this.$emit("quitar", id)
			},

			aumentar(producto) {
				this.$emit("aumentar", producto)
			},

      precios(producto) {
        const { precioVenta, precioVenta2, precioVenta3 } = producto
        return [precioVenta, precioVenta2, precioVenta3]
          .filter(precio => !!Number(precio))
      },

      formatoMonto(valor) {
        return Utiles.formatoMonto(valor)
      }
		}
	}
</script>
