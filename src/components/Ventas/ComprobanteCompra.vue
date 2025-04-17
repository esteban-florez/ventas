<template>
  <section>
    <div class="comprobante" :class="tamaño" id="comprobante">
      <div class="header carta-header" v-if="tamaño === 'carta'">
        <div class="logo-container">
          <img src="@/assets/ofertacaracas.jpg" class="logo">
        </div>
        <div class="empresa-info">
          <h1>Ofertas Caracas</h1>
        </div>
        <div class="factura-info">
          <p><b>Nota de Entrega: {{ venta.id }}</b></p>
          <p>Página: 1</p>
          <p>Fecha Emisión: {{ tiempo[0] }}</p>
          <p>Hora Emisión: {{ tiempo[1] }}</p>
          <p v-if="cuenta || apartado"><b>Vence en:</b> {{ venta.dias }} días</p>
        </div>
      </div>
      <div class="cliente-info">
        <p><b>Cliente:</b> <b>{{ venta.nombreCliente }}</b></p>
        <p><b>Numero telefónico:</b> <b>{{ venta.telefonoCliente }}</b></p>
        <p><b>Dirección:</b> <b>{{ venta.direccionCliente }}</b></p>
        <p><b>Atiende:</b> <b>{{ venta.nombreUsuario }}</b></p>
      </div>

      <table class="tabla-productos">
        <thead>
          <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>Precio Unitario</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(producto, index) in venta.productos" :key="index">
            <td>{{ producto.codigo }}</td>
            <td>{{ producto.nombre }}</td>
            <td>{{ producto.cantidad }}</td>
            <td>{{ producto.unidad }}</td>
            <td>${{ producto.precio }}</td>
            <td>${{ f(producto.precio * producto.cantidad) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="pago-container">
        <div class="pago-info">
          <p v-if="venta.delivery && !venta.delivery.gratis"><b>
            Delivery:</b>${{ f(venta.delivery.costo) }}
          </p>
          <p><b>Total:</b> ${{ f(venta.total) }}</p>
          <p v-if="!cotiza"><b>Su pago:</b> ${{ f(venta.pagado) }}</p>
          <p v-if="tipoVenta"><b>Cambio:</b> ${{ f(venta.pagado - venta.total) }}</p>
          <p v-if="cuenta || apartado"><b>Por pagar:</b> ${{ f(porPagar) }}</p>
        </div>
      </div>

      <p class="aviso-delivery" v-if="venta.delivery && !venta.delivery.gratis">
        <b>El monto del delivery está incluido en el total.</b>
      </p>
      <p><b>Gracias por su preferencia.</b></p>
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
    tiempo: '',
    titulo: '',
    nombre: '',
    telefono: '',
    cssText: `
      .comprobante {
        font-family: monospace;
        display: flex;
        flex-direction: column;
        font-size: 10px;
      }

      .carta-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
      }

      .logo-container {
        text-align: left;
        width: 30%;
      }

      .empresa-info {
        text-align: center;
        width: 40%;
      }

      .aviso-delivery {
        margin-bottom: -0.5rem;
      }

      .factura-info {
        text-align: right;
        width: 30%;
      }

      .logo {
        width: 100px;
        height: auto;
      }

      .cliente-info {
        text-align: left;
        font-weight: bold;
        margin-top: 10px;
      }

      .tabla-productos {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        border: 2px solid #000;
      }

      .tabla-productos th, .tabla-productos td {
        border: 2px solid #000;
        padding: 8px;
        text-align: center;
      }

      .tabla-productos thead {
        background-color: #f2f2f2;
        font-weight: bold;
      }

      .pago-container {
        display: flex;
        justify-content: flex-end;
        width: 100%;
        margin-top: 40px;
      }

      .pago-info {
        border: 2px solid #000;
        padding: 10px;
        width: 200px;
        font-weight: bold;
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
    this.tiempo = this.venta.fecha.split(' ')
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
