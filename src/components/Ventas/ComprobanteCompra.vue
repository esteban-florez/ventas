<template>
  <section>
    <div class="comprobante" :class="tamaño" id="comprobante" v-if="datosNegocio">
      <div class="header">
        <p><b>{{ titulo }}</b></p>
        <p>{{ datosNegocio.nombre }} Teléfono: {{ datosNegocio.telefono }}</p>
        <p><b>Cliente:</b>{{ venta.nombreCliente }}</p>
        <p><b>Atiende:</b>{{ venta.nombreUsuario }}</p>
        <p><b>Fecha: </b>{{ venta.fecha }}</p>
        <p v-if="cotiza"><b>Válido hasta: </b>{{ venta.hasta }}</p>
      </div>
      <table>
        <thead>
          <th style="text-align: start;">Producto</th>
          <th></th>
          <th>Total</th>
        </thead>
        <tbody>
          <tr v-for="(producto, index) in venta.productos" :key="index">
            <td class="ml-2">{{ producto.nombre }}</td>
            <td class="mr-2">${{ producto.precio }} X {{ producto.cantidad }} {{ producto.unidad }}.</td>
            <td>${{ f(producto.precio * producto.cantidad) }}</td>
          </tr>
        </tbody>
      </table>
      <p v-if="venta.delivery"><b>Delivery:</b>${{ f(venta.delivery.costo) }}</p>
      <p><b>Total:</b>${{ f(venta.total) }}</p>
      <p v-if="!cotiza"><b>Su pago:</b>${{ f(venta.pagado) }}</p>
      <p v-if="tipoVenta"><b>Cambio:</b>${{ f(venta.pagado - venta.total) }}</p>
      <p v-if="cuenta || apartado"><b>Por pagar:</b>${{ f(porPagar) }}</p>
      <p v-if="cuenta || apartado"><b>Vence en:</b> {{ venta.dias }} días</p>
      <p><b>Gracias por su preferencia</b></p>
      <p>----------------------------</p>
      <img :src="datosNegocio.logo" alt="Aqui el logo" width="120">
    </div>
  </section>
</template>
<script>
import Printd from 'printd';
import AyudanteSesion from '../../Servicios/AyudanteSesion'
import Utiles from '../../Servicios/Utiles'

export default {
  name: 'ComprobanteCompra',
  props: ['venta', 'tipo', 'porPagar', 'tamaño'],

  data: () => ({
    titulo: '',
    datosNegocio: null,
    cssText: `
      .comprobante {
        font-family: monospace;
        font-size: 22px;
      }

      .comprobante p {
        margin: 0!important;
        padding: 0!important;
        text-align: center;
      }

      .comprobante img {
        display: block;
        margin: 0 auto;
      }

      .comprobante th,
      .comprobante td {
        border-bottom: 1px solid #ddd;
        margin: 0!important;
        padding: 0!important;
      }

      .comprobante table {
        width: 100%;
      }

      .comprobante.carta .header p {
        text-align: center;
      }

      .comprobante.carta table {
        margin: 1rem;
      }

      .comprobante.carta p {
        text-align: left;
      }

      .comprobante.tiquera {
        width: 250px;
        font-size: 14px;
      }
      
      .comprobante.tiquera th,
      .comprobante.tiquera td {
        font-size: 12px !important;
      }
    `,
  }),

  beforeMount() {
    this.generarTitulo()
    this.obtenerDatosNegocio()
  },

  mounted() {
    this.d = new Printd();
    this.imprimir();
  },

  computed: {
    cuenta() {
      return this.tipo === 'cuenta'
    },
    apartado() {
      return this.tipo === 'apartado'
    },
    cotiza() {
      return this.tipo === 'cotiza'
    },
    tipoVenta() {
      return this.tipo === 'venta'
    },
  },

  methods: {
    generarTitulo() {
      switch (this.tipo) {
        case 'venta':
          this.titulo = 'COMPROBANTE DE COMPRA'
          break

          case 'cuenta':
          this.titulo = 'COMPROBANTE DE CUENTA'
          break

        case 'apartado':
          this.titulo = 'COMPROBANTE DE APARTADO'
          break

        case 'cotiza':
          this.titulo = 'COTIZACIÓN'
          break

        default:
          this.titulo = 'COMPROBANTE'
          break
      }
    },

    f(number) {
      return Number(number).toFixed(2)
    },

    obtenerDatosNegocio() {
      this.datosNegocio = AyudanteSesion.obtenerDatosNegocio()
      this.datosNegocio.logo = Utiles.regresarRuta() + this.datosNegocio.logo
    },

    imprimir() {
      let zona = document.getElementById('comprobante');
      setTimeout(() => this.d.print(zona, [this.cssText]), 10);
      this.$emit('impreso', false);
    },
  }
}
</script>
