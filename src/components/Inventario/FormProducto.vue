<template>
  <section class="box">
    <b-field grouped group-multiline>
      <b-field label="Código de barras" expanded>
        <b-input type="number" icon="barcode" placeholder="Código de barras" v-model="producto.codigo"></b-input>
      </b-field>
      <b-field label="Nombre" expanded>
        <b-input placeholder="Nombre o descripción del producto" v-model="producto.nombre"></b-input>
      </b-field>
      <b-field label="Unidad" expanded>
        <b-select class="wide" placeholder="Unidad" icon="tag-multiple" v-model="producto.unidad">
          <option v-for="[value, title] in unidades" :key="value" :value="value">
            {{ title }}
          </option>
        </b-select>
      </b-field>
      <b-field label="Precio compra" expanded>
        <b-input step="any" icon="currency-usd" type="number" placeholder="Precio de compra"
          v-model="producto.precioCompra"></b-input>
      </b-field>
    </b-field>

    <b-field class="mt-5" grouped group-multiline>
      <b-field label="Precio venta" expanded>
        <b-input step="any" icon="currency-usd" type="number" placeholder="Precio de venta"
          v-model="producto.precioVenta" :min="producto.precioCompra"></b-input>
      </b-field>

      <b-field label="Precio venta 2" expanded>
        <b-input step="any" icon="currency-usd" type="number" placeholder="Precio de venta 2"
          v-model="producto.precioVenta2" :min="producto.precioCompra"></b-input>
      </b-field>

      <b-field label="Precio venta 3" expanded>
        <b-input step="any" icon="currency-usd" type="number" placeholder="Precio de venta 3"
          v-model="producto.precioVenta3" :min="producto.precioCompra"></b-input>
      </b-field>

      <b-field label="Precio venta 4" expanded>
        <b-input step="any" icon="currency-usd" type="number" placeholder="Precio de venta 4"
          v-model="producto.precioVenta4" :min="producto.precioCompra"></b-input>
      </b-field>

      <b-field label="Existencia" expanded v-if="!editar">
        <b-numberinput min="1" step="0.01" type="is-info" placeholder="Existencia" v-model="producto.existencia">
        </b-numberinput>
      </b-field>
    </b-field>
    <br>
    <b-field grouped group-multiline>
      <b-field>
        <b-switch v-model="producto.vendidoMayoreo" type="is-info">
          ¿Vendido al mayoreo?
        </b-switch>
      </b-field>

      <b-field label="Precio mayoreo" expanded v-if="producto.vendidoMayoreo">
        <b-input step="any" icon="currency-usd" type="number" placeholder="Precio de venta al mayoreo"
          v-model="producto.precioMayoreo" :min="producto.precioCompra"></b-input>
      </b-field>

      <b-field label="Cantidad mayoreo" expanded v-if="producto.vendidoMayoreo">
        <b-numberinput min="1" type="is-info" placeholder="Cantidad para aplicar mayoreo"
          v-model="producto.cantidadMayoreo">
        </b-numberinput>
      </b-field>
    </b-field>
    <br>
    <b-field grouped group-multiline>

      <b-field label="Selecciona un proveedor">
        <b-select placeholder="Proveedor" icon="factory" v-model="producto.proveedor">
          <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
            {{ proveedor.nombre }}
          </option>
        </b-select>
      </b-field>
    </b-field>
    <br>
    <div class="buttons has-text-centered">
      <b-button type="is-primary" size="is-large" icon-left="check" @click="registrar">Registrar</b-button>
      <b-button type="is-dark" size="is-large" icon-left="cancel" tag="router-link" to="/inventario">Cancelar</b-button>
    </div>
    <errores-component :errores="mensajesError" v-if="mensajesError.length > 0" />
  </section>
</template>
<script>
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'
import ErroresComponent from '../Extras/ErroresComponent'
import { UNIDADES } from '@/consts'
export default {
  name: "FormProducto",
  props: ['productoProp', 'editar'],
  components: { ErroresComponent },

  data: () => ({
    categorias: [],
    marcas: [],
    proveedores: [],
    producto: {
      codigo: '',
      nombre: '',
      unidad: null,
      precioCompra: '',
      precioVenta: '',
      precioVenta2: '',
      precioVenta3: '',
      precioVenta4: '', 
      existencia: 0,
      vendidoMayoreo: false,
      precioMayoreo: '',
      cantidadMayoreo: 0,
      categoria: null,
      marca: null,
      proveedor: null,
    },
    mensajesError: [],
    unidades: Object.entries(UNIDADES),
  }),

  mounted() {
    this.obtenerCategorias()
    this.obtenerMarcas()
    this.obtenerProveedores()
    if (this.productoProp) {
      this.producto = this.productoProp
      this.producto.vendidoMayoreo = this.productoProp.vendidoMayoreo === 1
      this.producto.cantidadMayoreo = parseInt(this.productoProp.cantidadMayoreo)
    }
  },

  methods: {
    registrar() {
      let producto = {
        "Código": this.producto.codigo,
        "Nombre": this.producto.nombre,
        "Precio compra": this.producto.precioCompra,
        "Precio venta": this.producto.precioVenta,
        "Proveedor": this.producto.proveedor,
      }

      if (this.editar) {
        producto.Existencia = this.producto.existencia
      }

      this.mensajesError = Utiles.validarDatos(producto)
      if (parseFloat(this.producto.precioVenta) < parseFloat(this.producto.precioCompra)) {
        this.mensajesError.push("El precio de venta debe ser mayor al precio compra")
      }
      if (this.mensajesError.length > 0) return

      if (!this.producto.vendidoMayoreo) {
        this.producto.cantidadMayoreo = ""
      }

      this.$emit("registrado", this.producto)
      this.producto = {
        codigo: "",
        nombre: "",
        precioCompra: "",
        precioVenta: "",
        precioVenta2: "",
        precioVenta3: "",
        precioVenta4: "",
        existencia: 0,
        vendidoMayoreo: false,
        precioMayoreo: "",
        cantidadMayoreo: 0,
        categoria: "",
        marca: ""
      }
    },

    obtenerMarcas() {
      let payload = {
        accion: 'obtener'
      }
      HttpService.obtenerConConsultas('marcas.php', payload)
        .then(marcas => {
          this.marcas = marcas
        })
    },                  

    obtenerCategorias() {
      let payload = {
        accion: 'obtener'
      }
      HttpService.obtenerConConsultas('categorias.php', payload)
        .then(categorias => {
          this.categorias = categorias
        })
    },

    obtenerProveedores() {
      let payload = {
        accion: 'obtener'
      }
      HttpService.obtenerConConsultas('proveedores.php', payload)
        .then(proveedores => {
          this.proveedores = proveedores
        })
    },
  }

}
</script>
