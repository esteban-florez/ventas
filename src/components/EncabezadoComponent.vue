<template>
  <b-navbar type="is-warning" class="fondo" v-if="usuario">
    <template #brand>
      <b-navbar-item tag="router-link" :to="{ path: '/' }">
        <img src="@/assets/ofertacaracas.jpg">
      </b-navbar-item>
    </template>
    <template #start>
      <b-navbar-item tag="router-link" :to="{ path: '/' }">
        <b-icon icon="view-dashboard" size="is-medium">
        </b-icon>
        <span></span>
        Inicio
      </b-navbar-item>
      <b-navbar-item tag="router-link" :to="{ path: '/inventario' }" v-if="permisos['vistas.inventario']">
        <b-icon icon="chart-bar-stacked" size="is-medium">
        </b-icon>
        <span></span>
        Inventario
      </b-navbar-item>

      <b-navbar-item tag="router-link" :to="{ path: '/marcas-y-categorias' }" v-if="permisos['vistas.marcas_categorias']">
        <b-icon icon="archive" size="is-medium">
        </b-icon>
        <span></span>
        Marcas y categorías
      </b-navbar-item>

      <b-navbar-item tag="router-link" :to="{ path: '/realizar-venta' }" v-if="permisos['vistas.vender']">
        <b-icon icon="cash-fast" size="is-medium">
        </b-icon>
        <span></span>
        Vender
      </b-navbar-item>

      <b-navbar-item v-if="reportes">
        <b-icon icon="file-chart-outline" size="is-medium" class="is-pulled-left">
        </b-icon>
        <b-navbar-dropdown label="Reportes" icon="home">
          <b-navbar-item tag="router-link" :to="{ path: '/reporte-ventas' }">
            <b-icon icon="cash-register" v-if="permisos['vistas.ventas']">
            </b-icon>
            <span></span>
            Ventas
          </b-navbar-item>
          <b-navbar-item tag="router-link" :to="{ path: '/reporte-cuentas' }" v-if="permisos['vistas.cuentas']">
            <b-icon icon="wallet">
            </b-icon>
            <span></span>
            Cuentas
          </b-navbar-item>
          <b-navbar-item tag="router-link" :to="{ path: '/reporte-apartados' }" v-if="permisos['vistas.apartados']">
            <b-icon icon="wallet-travel">
            </b-icon>
            <span></span>
            Apartados
          </b-navbar-item>
          <b-navbar-item tag="router-link" :to="{ path: '/reporte-cotizaciones' }" v-if="permisos['vistas.cotizaciones']">
            <b-icon icon="ticket-outline">
            </b-icon>
            <span></span>
            Cotizaciones
          </b-navbar-item>
          <b-navbar-item tag="router-link" :to="{ path: '/deliveries' }" v-if="permisos['vistas.deliveries']">
            <b-icon icon="truck-delivery">
            </b-icon>
            <span></span>
            Deliveries
          </b-navbar-item>
          <b-navbar-item tag="router-link" :to="{ path: '/choferes' }" v-if="permisos['vistas.choferes']">
            <b-icon icon="card-account-details">
            </b-icon>
            <span></span>
            Choferes
          </b-navbar-item>
          <b-navbar-item tag="router-link" :to="{ path: '/historial' }" v-if="permisos['vistas.historial']">
            <b-icon icon="clipboard-text-clock">
            </b-icon>
            <span></span>
            Movimientos
          </b-navbar-item>
          <b-navbar-item tag="router-link" :to="{ path: '/proveedores' }" v-if="permisos['vistas.proveedores']">
            <b-icon icon="factory">
            </b-icon>
            <span></span>
            Proveedores
          </b-navbar-item>
        </b-navbar-dropdown>
      </b-navbar-item>

      <b-navbar-item tag="router-link" :to="{ path: '/clientes' }" v-if="permisos['vistas.clientes']">
        <b-icon icon="account-supervisor" size="is-medium">
        </b-icon>
        <span></span>
        Clientes
      </b-navbar-item>

    </template>

    <template #end>
      <b-navbar-item v-if="configuracion">
        <b-icon icon="cogs" size="is-medium" class="is-pulled-left">
        </b-icon>
        <b-navbar-dropdown label="Configurar" icon="home">
          <b-navbar-item tag="router-link" :to="{ path: '/metodos' }" v-if="permisos['vistas.metodos']">
            <b-icon icon="wallet">
            </b-icon>
            <span></span>
            Métodos de pago
          </b-navbar-item>
          <b-navbar-item tag="router-link" :to="{ path: '/usuarios' }" v-if="permisos['vistas.usuarios']">
            <b-icon icon="account">
            </b-icon>
            <span></span>
            Usuarios
          </b-navbar-item>
          <b-navbar-item tag="router-link" :to="{ path: '/roles' }" v-if="permisos['vistas.roles']">
            <b-icon icon="cog">
            </b-icon>
            <span></span>
            Roles
          </b-navbar-item>
        </b-navbar-dropdown>
      </b-navbar-item>

      <b-navbar-item tag="router-link" :to="{ path: '/perfil' }">
        <b-icon icon="account" size="is-medium">
        </b-icon>
        <span> </span>
        {{ usuario.usuario }}
      </b-navbar-item>
      <b-navbar-item>
        <div class="buttons">
          <a class="button is-light" @click="salir">
            Salir
          </a>
        </div>
      </b-navbar-item>
    </template>
  </b-navbar>
</template>
<script>
import HttpService from '@/Servicios/HttpService'

export default {
  name: "EncabezadoComponent",
  props: ['usuario', 'permisos'],

  methods: {
    async salir() {
      await HttpService.obtenerConConsultas('usuarios.php', { accion: 'cerrar_sesion' })
      this.$emit('cierre')
    }
  },

  computed: {
    configuracion() {
      const configuraciones = ['metodos', 'usuarios', 'roles']
      return configuraciones.some(config => this.permisos[`vistas.${config}`])
    },

    reportes() {
      const reportes = ['ventas', 'cuentas', 'apartados', 'cotizaciones', 'deliveries', 'choferes', 'movimientos', 'proveedores']
      return reportes.some(reporte => this.permisos[`vistas.${reporte}`])
    },
  },
}
</script>
