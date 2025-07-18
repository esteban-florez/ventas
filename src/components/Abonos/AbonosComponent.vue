<template>
  <section>
    <nav-component :titulo="'Abonos'" texto="Realizar abono" :link="link" />
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item active>Abonos</b-breadcrumb-item>
    </b-breadcrumb>
    <h3 class="has-text-centered is-size-4 mb-3 has-text-weight-bold">
      Datos de {{ this.cuentaApartado && this.cuentaApartado.tipo === 'cuenta' ? 'cuenta' : 'apartado' }}
    </h3>
    <cartas-totales :totales="datosCuentaApartado" />
    <hr>
    <mensaje-inicial :titulo="'No se han registrado abonos'" :subtitulo="'Haz click el botón de la esquina para registrar un nuevo abono'" v-if="abonos.length < 1" />
    <template v-else>
      <h3 class="has-text-centered is-size-4 mb-3 has-text-weight-bold">
        Lista de abonos
      </h3>
      <div class="columns">
        <div class="column">
          <b-select v-model="perPage">
            <option value="5">5 por página</option>
            <option value="10">10 por página</option>
            <option value="15">15 por página</option>
            <option value="20">20 por página</option>
          </b-select>
        </div>
        <div class="column is-flex is-justify-content-end">
          <b-button type="is-primary" tag="a" :href="`#/pdf/abonos/${cuentaApartado && cuentaApartado.id}`"
            target="__blank" rel="noopener noreferrer">
            Imprimir
          </b-button>
        </div>
      </div>
      <b-table class="box" :data="abonos" :per-page="perPage" :paginated="true" :pagination-simple="false"
        :pagination-position="'bottom'" :default-sort-direction="'asc'" :pagination-rounded="true">
        
        
        <b-table-column field="fecha" label="Fecha" v-slot="props">
          {{ formatoFechaCaracas(props.row.fecha) }}
        </b-table-column>

        <b-table-column field="monto" label="Monto" sortable searchable v-slot="props">
          ${{ formatoMonto(props.row.monto) }}
        </b-table-column>

        <b-table-column field="metodo" label="Método de pago" sortable searchable v-slot="props">
          {{ props.row.metodo || props.row.simple }}
        </b-table-column>

        <b-table-column field="origen" label="Origen" sortable searchable v-slot="props">
          {{ props.row.origen || 'N/A' }}
        </b-table-column>

        <b-table-column field="ticket" label="Comprobante" v-slot="props">
          <b-button type="is-warning" icon-left="pencil-outline" tag="router-link"
            :to="{ name: 'EditarAbono', params: { id: props.row.id } }" v-if="can('abonos.editar')">Editar</b-button>
          <b-button type="is-info" @click="generarComprobante(props)">
            <b-icon icon="ticket-outline">
            </b-icon>
          </b-button>
          <b-button type="is-danger" icon-left="trash-can-outline"
            @click="eliminarAbono(props.row)">Eliminar</b-button>
        </b-table-column>
      </b-table>
    </template>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import MensajeInicial from '../Extras/MensajeInicial'
import NavComponent from '../Extras/NavComponent'
import HttpService from '../../Servicios/HttpService'
import CartasTotales from '../Extras/CartasTotales.vue'
import Utiles from '../../Servicios/Utiles'

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
    if (Number(this.cuentaApartado.porPagar) === 0 && this.can('abonos.registrar')) return
    this.link = { name: 'RealizarAbono', params: { id: this.$route.params.id } }
  },

  methods: {
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
    },

    generarComprobante({ row }) {
      const datos = { abono: row, cuenta: this.cuentaApartado }
      localStorage.setItem('comprobante-abono', JSON.stringify(datos))
      const ruta = this.$router.resolve({ name: 'PDFAbono' })
      window.open(ruta.href, '_blank')
    },

    async eliminarAbono(abono) {
      this.$buefy.dialog.confirm({
        message: '¿Estás seguro de que deseas eliminar este abono?',
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
        type: 'is-danger',
        onConfirm: async () => {
          this.cargando = true
          try {
            const respuesta = await HttpService.obtenerConConsultas('ventas.php', {
              accion: 'eliminar_abono',
              id: abono.id
            })
            if (respuesta || respuesta.success) {
              this.$buefy.toast.open({
                message: 'Abono eliminado correctamente',
                type: 'is-success'
              })
              this.obtenerAbonos()
            } else {
              this.$buefy.toast.open({
                message: respuesta.message || 'No se pudo eliminar el abono',
                type: 'is-danger'
              })
            }
          } catch (e) {
            this.$buefy.toast.open({
              message: 'Error al eliminar el abono',
              type: 'is-danger'
            })
          } finally {
            this.cargando = false
          }
        }
      })
    },

    formatoFechaCaracas(fecha) {
      if (!fecha) return 'Fecha inválida';
      const [anio, mes, dia] = fecha.split('-');
      return `${dia}-${mes}-${anio}`;
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
          nombre: 'Nombre del Cliente',
          total: cuentaApartado.nombreCliente,
          icono: 'account',
          clase: 'has-text-primary',
        },
        {
          nombre: 'N0 Factura',
          total: cuentaApartado.id,
          icono: 'account',
          clase: 'has-text-primary',
        },
        {
          nombre: 'Monto total',
          total: this.formatoMonto(cuentaApartado.total),
          icono: 'cash',
          clase: 'has-text-info',
        },
        {
          nombre: 'Monto pagado',
          total: this.formatoMonto(cuentaApartado.pagado),
          icono: 'check-circle',
          clase: 'has-text-success',
        },
        {
          nombre: 'Monto por pagar',
          total: this.formatoMonto(cuentaApartado.porPagar),
          icono: 'clock',
          clase: 'has-text-danger',
        },
      ]

      this.cargando = false
    }
  }
}
</script>
