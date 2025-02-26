<template>
  <section id="pdf">
    <div class="comprobante" v-if="datosNegocio">
      <h1><b>Comprobante de Abono</b></h1>
      <p>{{ datosNegocio.nombre }} Teléfono: {{ datosNegocio.telefono }}</p>
      <p><b>Fecha: </b>{{ abono.fecha }}</p>

      <div class="parrafo">
        <p>
          El día <b>{{ fecha }}</b> el cliente <b>{{ cuenta.nombreCliente }}</b>, realizó un abono por un monto de <b>${{ abono.monto }}</b>, tras lo cual su el monto restante es de <b>${{ cuenta.porPagar }}</b>.
        </p>
      </div>

      <div class="firmas">
        <div>
          <p>_________________________</p>
          <p>Firma del cliente</p>
        </div>
        <div>
          <p>_________________________</p>
          <p>Firma del dueño</p>
        </div>
      </div>

      <p><b>Gracias por su preferencia</b></p>
      <p>----------------------------</p>
      <img :src="datosNegocio.logo" alt="Aqui el logo" width="120">
    </div>
  </section>
</template>

<script>
import Printd from 'printd'
import AyudanteSesion from '../../Servicios/AyudanteSesion'
import Utiles from '../../Servicios/Utiles'

export default {
  name: 'PDFAbono',

  data: () => ({
    titulo: '',
    cuenta: null,
    abono: null,
    datosNegocio: null,
  }),

  async mounted() {
    this.obtenerDatosNegocio()
    document.body.style.opacity = '0'

    const json = localStorage.getItem('comprobante-abono')
    const { cuenta, abono } = JSON.parse(json)
    this.cuenta = cuenta
    this.abono = abono
    localStorage.removeItem('comprobante-abono')
    
    const d = new Printd()
    d.onAfterPrint(() => window.close())
    
    new Promise(res => setTimeout(res, 1000))
      .then(() => {
        const comprobante = document.querySelector('#pdf')
        d.print(comprobante, [`
          h1 {
            text-align: center;
          }

          .comprobante {
            font-size: 22px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
          }
    
          .comprobante p {
            margin: 0;
            padding: 0;
            text-align: center;
          }
    
          .comprobante img {
            display: block;
            margin: 0 auto;
          }
    
          .parrafo {
            margin-top: 2rem;
            display: block;
          }
    
          .firmas {
            display: flex;
            justify-content: space-around;
            margin-top: 3rem;
            margin-bottom: 3rem;
          }
        `])
      })
},

  methods: {
    obtenerDatosNegocio() {
      this.datosNegocio = AyudanteSesion.obtenerDatosNegocio()
      this.datosNegocio.logo = Utiles.regresarRuta() + this.datosNegocio.logo
    },
  },

  computed: {
    fecha() {
      return new Intl.DateTimeFormat('es', {
        dateStyle: 'long',
      }).format(new Date(this.abono.fecha))
    },
  }
}
</script>
