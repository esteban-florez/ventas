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
				${{ props.row.precio }}
			</b-table-column>

			<b-table-column field="cantidad" label="Cantidad" v-slot="props">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <b-field class="mb-0">
            <b-numberinput @input="aumentar(props.row)" min="1" :max="props.row.existencia" v-model="props.row.cantidad" style="width: 13em">
            </b-numberinput>
          </b-field>
          <span>
            {{ unidades[props.row.unidad].toLowerCase() }}
          </span>
        </div>
			</b-table-column>

			<b-table-column field="subtotal" label="Subtotal" v-slot="props">
				<b>${{ props.row.precio * props.row.cantidad }}</b>
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

	export default {
		name: "TablaProductos",
		props: ["listaProductos"],

		data:()=>({
      unidades: UNIDADES
		}),

		methods: {
			quitar(id){
				this.$emit("quitar", id)
			},

			aumentar(producto){
				this.$emit("aumentar", producto)
			}
		}
	}
</script>
