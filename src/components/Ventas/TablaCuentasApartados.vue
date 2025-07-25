<template>
  <section>
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
          <b-button type="is-primary" tag="a" :href="printHref" @click="cargarRegistrosFiltrados" target="__blank" rel="noopener noreferrer">
            Imprimir Cuentas
          </b-button>
        </div>
        <div class="column is-flex is-justify-content-end">
          <b-button type="is-primary" icon-left="printer" @click="imprimir">
            Imprimir todos los abonos
          </b-button>
        </div>
      </div>

    <b-table class="box" :data="datos" :paginated="isPaginated" :per-page="perPage" :current-page.sync="currentPage"
      :pagination-simple="isPaginationSimple" :pagination-position="paginationPosition"
      :default-sort-direction="defaultSortDirection" :pagination-rounded="isPaginationRounded" :sort-icon="sortIcon"
      :sort-icon-size="sortIconSize" default-sort="user.first_name" aria-next-label="Next page"
      aria-previous-label="Previous page" aria-page-label="Page" aria-current-label="Current page">
      
      <b-table-column field="id" label="N0 Factura" sortable searchable v-slot="props">
        {{ props.row.id }}
      </b-table-column>

      <b-table-column field="nombreCliente" label="Cliente" sortable searchable v-slot="props">
        {{ props.row.nombreCliente }}
      </b-table-column>

      <b-table-column field="fecha" label="Fecha" sortable searchable v-slot="props">
        {{ formatoFechaCaracas(props.row.fecha) }}
      </b-table-column>

      <b-table-column field="porPagar" label="Por pagar" sortable v-slot="props">
        <span class="has-text-danger has-text-weight-bold"> ${{ formatoMontoLocal(props.row.porPagar) }}</span>
      </b-table-column>

      <b-table-column field="dias" label="Duración" sortable v-slot="props" v-if="datos[0].tipo === 'cuenta'">
        {{ props.row.dias }} días
      </b-table-column>

      <b-table-column field="estado" label="Estado" sortable searchable v-slot="props">
        <span class="tag is-success is-large" v-if="obtenerEstadoCuenta(props.row) === 'LIQUIDADO'">LIQUIDADO</span>
        <span class="tag is-warning is-large" v-if="obtenerEstadoCuenta(props.row) === 'VIGENTE'">VIGENTE</span>
        <span class="tag is-danger is-large" v-if="obtenerEstadoCuenta(props.row) === 'VENCIDA'">VENCIDA</span>
      </b-table-column>

      <b-table-column field="abonos" label="Abonos" v-slot="props">
        <b-button type="is-warning" tag="router-link" :to="{ name: 'AbonosComponent', params: { id: props.row.id } }" v-if="can('vistas.abonos')">
          <b-icon icon="format-list-bulleted">
          </b-icon>
        </b-button>
      </b-table-column>

      <b-table-column field="ticket" label="Comprobante" v-slot="props">
        <b-button type="is-info" @click="generarComprobante(props.row)">
          <b-icon icon="ticket-outline"></b-icon>
        </b-button>
      </b-table-column>

      <b-table-column field="abonar" label="Abonar" v-slot="props" v-if="can('ventas.realizar_abono')">
        <b-button type="is-primary" tag="router-link" :to="{ name: 'RealizarAbono', params: { id: props.row.id } }" v-if="props.row.porPagar > 0">
          <b-icon icon="wallet-plus">
          </b-icon>
        </b-button>
      </b-table-column>

      <b-table-column field="acciones" label="Acciones" v-slot="props">
        <b-button type="is-warning" icon-left="pencil-outline" tag="router-link"
          :to="{ name: 'EditarCuenta', params: { id: props.row.id }, query: { tipo: linkTipo } }" 
          v-if="can('cuentas.editar')">Editar</b-button>
          <b-button type="is-danger" icon-left="trash-can-outline"
            @click="eliminarCuenta(props.row)" v-if="can('apartados.eliminar','cuentas.eliminar')">Eliminar</b-button>
        </b-table-column>
    </b-table>
  </section>
</template>
<script>
import HttpService from '@/Servicios/HttpService'

export default {
  props: ["datos", "printHref", "formatoMonto", "filtros"],

  data: () => ({
    isPaginated: true,
    isPaginationSimple: false,
    isPaginationRounded: true,
    paginationPosition: 'bottom',
    defaultSortDirection: 'asc',
    sortIcon: 'arrow-up',
    sortIconSize: 'is-medium',
    currentPage: 1,
    perPage: 5,
  }),

  methods: {
    cargarRegistrosFiltrados() {
      this.$emit('cargarRegistrosFiltrados')
    },

    generarComprobante(item) {
      this.$emit("imprimir", item)
    },

    formatoMontoLocal(valor) {
      // Usa la función recibida como prop para formatear el monto
      return this.formatoMonto ? this.formatoMonto(valor) : valor
    },

    async eliminarCuenta(cuenta) {
      this.$buefy.dialog.confirm({
        message: '¿Estás seguro de que deseas eliminar esta cuenta?',
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
        type: 'is-danger',
        onConfirm: async () => {
          this.cargando = true
          try {
            const respuesta = await HttpService.obtenerConConsultas('ventas.php', {
              accion: 'eliminar_cuenta',
              id: cuenta.id
            })
            if (respuesta || respuesta.success) {
              this.$buefy.toast.open({
                message: 'Cuenta eliminada correctamente',
                type: 'is-success'
              })
              this.$emit('actualizar-cuentas')
            } else {
              this.$buefy.toast.open({
                message: respuesta.message || 'No se pudo eliminar la cuenta',
                type: 'is-danger'
              })
            }
          } catch (e) {
            this.$buefy.toast.open({
              message: 'Error al eliminar la cuenta',
              type: 'is-danger'
            })
          } finally {
            this.cargando = false
          }
        }
      })
    },  

    imprimir() {
      // Usa la prop filtros, no this.filtros
      localStorage.setItem('filtros-abonos', JSON.stringify(this.filtros || {}))
      const ruta = this.$router.resolve({ name: 'PDFTodosAbonos' })
      window.open(ruta.href, '_blank')
    },

    obtenerEstadoCuenta(cuenta) {
      if (cuenta.porPagar < 1) return 'LIQUIDADO'
      const fechaCreacion = new Date(cuenta.fecha.replace(' ', 'T'))
      const fechaVencimiento = new Date(fechaCreacion)
      fechaVencimiento.setDate(fechaVencimiento.getDate() + Number(cuenta.dias || 0))
      const hoy = new Date()
      hoy.setHours(0,0,0,0)
      if (hoy > fechaVencimiento) return 'VENCIDA'
      return 'VIGENTE'
    },

    formatoFechaCaracas(fecha) {
      // Forzar la fecha como Caracas (-04:00)
      return new Date(fecha.replace(' ', 'T') + '-04:00').toLocaleDateString(
        'es-ES',
        { day: '2-digit', month: '2-digit', year: 'numeric', }
      ).replace(/\//g, '-')
    },
  },

  computed: {
    linkTipo() {
      return this.$route.name === 'ReporteApartados' ? 'apartado' : 'cuenta';
    },
    tipo() {
      if (this.datos) {
        return this.datos[0].tipo
      }

      return ''
    }
  },
}
</script>
