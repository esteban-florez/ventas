<template>
  <section>
    <nav-component :titulo="'Abonos'" texto="Realizar abono" :link="link"/>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Abonos</b-breadcrumb-item>
    </b-breadcrumb>
    <mensaje-inicial :titulo="'No se han registrado abonos'" v-if="abonos.length < 1" />
    <h3 class="has-text-centered is-size-4 mb-3 has-text-weight-bold">
      Datos de {{ this.cuentaApartado.tipo === 'cuenta' ? 'cuenta' : 'apartado' }}
    </h3>
    <cartas-totales :totales="datosCuentaApartado" />
    <hr>
    <h3 class="has-text-centered is-size-4 mb-3 has-text-weight-bold">
      Lista de abonos
    </h3>
    <b-select v-model="perPage">
        <option value="5">5 por página</option>
        <option value="10">10 por página</option>
        <option value="15">15 por página</option>
        <option value="20">20 por página</option>
      </b-select>
    <b-table class="box" :data="abonos" :per-page="perPage" :paginated="true" :pagination-simple="false" :pagination-position="'bottom'"
    :default-sort-direction="'asc'" :pagination-rounded="true">
      <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
        {{ props.row.fecha }}
      </b-table-column>

      <b-table-column field="monto" label="Monto" sortable searchable v-slot="props">
        ${{ props.row.monto }}
      </b-table-column>

      <b-table-column field="metodo" label="Método de pago" sortable searchable v-slot="props">
        {{ props.row.metodo || props.row.simple }}
      </b-table-column>

      <b-table-column field="origen" label="Origen" sortable searchable v-slot="props">
        {{ props.row.origen || 'N/A' }}
      </b-table-column>

      <b-table-column field="ticket" label="Comprobante" v-slot="props">
        <b-button type="is-info" @click="generarComprobante(props)">
          <b-icon icon="ticket-outline">
          </b-icon>
        </b-button>
      </b-table-column>
    </b-table>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import MensajeInicial from '../Extras/MensajeInicial'
import NavComponent from '../Extras/NavComponent'
import HttpService from '../../Servicios/HttpService'
import CartasTotales from '../Extras/CartasTotales.vue';

export default {
  name: "AbonosComponent",
  components: { MensajeInicial, NavComponent, CartasTotales },

  data: () => ({
    cargando: false,
    abonos: [],
    perPage: 5,
    cuentaApartado: null,
    datosCuentaApartado: [],
    link: null,
  }),

  async mounted() {
    await this.obtenerAbonos()
    if (Number(this.cuentaApartado.porPagar) === 0) return
    this.link = { name: 'RealizarAbono', params: { id: this.$route.params.id } }
  },

  methods: {
    generarComprobante(props) {
      alert('TODO -> imprimir comprobante de abono', props)
    },

    async obtenerAbonos() {
      this.cargando = true
      let payload = {
        accion: 'obtener_abonos',
        id: this.$route.params.id,
      }

      const datos = await HttpService.obtenerConConsultas('ventas.php', payload)
      const { abonos, cuentaApartado } = datos

      this.abonos = abonos
      this.cuentaApartado = cuentaApartado
      this.datosCuentaApartado = [
        {
          nombre: 'Monto total',
          total: cuentaApartado.total,
          icono: 'cash',
          clase: 'has-text-info',
        },
        {
          nombre: 'Monto pagado',
          total: cuentaApartado.pagado,
          icono: 'check-circle',
          clase: 'has-text-success',
        },
        {
          nombre: 'Monto por pagar',
          total: cuentaApartado.porPagar,
          icono: 'clock',
          clase: 'has-text-danger',
        },
        {
          nombre: 'Nombre del Cliente',
          total: cuentaApartado.nombreCliente,
          icono: 'account',
          clase: 'has-text-primary',
        },
      ]

      this.cargando = false
    }
  }
}
</script>
