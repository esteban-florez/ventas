<template>
  <section>
    <div class="comprobante" :class="tamaño" id="comprobante">
      <div class="header">
        <p><b>{{ titulo }}</b></p>
        <p>{{ nombre }}</p>
        <p>Teléfono: {{ telefono }}</p>
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
    </div>
  </section>
</template>
<script>
import Printd from 'printd'
import html2pdf from 'html2pdf.js'

export default {
  name: 'ComprobanteCompra',
  props: ['venta', 'tipo', 'porPagar', 'tamaño', 'enviarCliente'],

  data: () => ({
    titulo: '',
    nombre: '',
    telefono: '',
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
    const { VUE_APP_OWNER_NAME, VUE_APP_OWNER_PHONE } = process.env
    this.nombre = VUE_APP_OWNER_NAME
    this.telefono = VUE_APP_OWNER_PHONE
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

    async imprimir() {
      let zona = document.getElementById('comprobante')

      const options = {
        margin: 5,
        filename: 'Comprobante.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
      }

      setTimeout(async () => {
        this.d.onAfterPrint(async () => {
          const telefono = this.venta.telefonoCliente
          if (!this.enviarCliente || !telefono) return
          const comprobante = html.querySelector('#comprobante')
          if (this.tamaño === 'tiquera') {
            comprobante.classList.remove('tiquera')
            comprobante.classList.add('carta')
          }

          const pdf = await html2pdf()
            .from(html)
            .set(options)
            .outputPdf('blob')

          if (this.tamaño === 'tiquera') {
            comprobante.classList.add('tiquera')
            comprobante.classList.remove('carta')
          }

          const formData = new FormData()
          formData.set('pdf', pdf)

          const { VUE_APP_API_URL: API_URL } = process.env
          await fetch(`${API_URL}/archivo.php?numero=${telefono}`, {
            method: 'POST',
            body: formData,
            credentials: 'include',
          })
        })

        this.d.print(zona, [this.cssText])

        const iframe = this.d.getIFrame()
        const { contentDocument } = iframe
        const html = contentDocument.querySelector('html')

        this.$emit('impreso', false)
      }, 10)

    },
  }
}
</script>
