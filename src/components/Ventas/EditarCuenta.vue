<template>
  <section>
    <buscar-producto @seleccionado="onSeleccionado" />
    <mensaje-inicial :titulo="'No has agregado productos'"
      :subtitulo="'Agrega algunos productos a la lista para venderlos :)'" v-if="productos.length < 1" />
    <div v-if="productos.length > 0">
      <tabla-productos :listaProductos="productos" @quitar="onQuitar" @aumentar="onAumentar"
        @precioCambiado="calcularTotal" />
      <div class="notification is-primary mt-3">
        <p class=" has-text-weight-bold has-text-centered" style="font-size:5em">
          Total ${{ formatoMonto(total) }}
        </p>
         <nav class="level mt-2">
          <div class="level-item has-text-centered" v-if="can('ventas.registrar_venta')">
            <b-button class="button is-responsive" type="is-success" inverted icon-left="check" size="is-large"
              @click="abrirDialogo()">
              Actualizar {{ tipo }}
            </b-button>
          </div>
          <div class="level-item has-text-centered">
            <b-button class="button is-responsive" type="is-danger" inverted icon-left="cancel" size="is-large"
              @click="cancelarVenta">
              Cancelar
            </b-button>
          </div>
        </nav>
      </div>
    </div>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
    <b-modal v-model="mostrarDialogo" has-modal-card trap-focus :destroy-on-hide="false" aria-role="dialog"
      aria-label="Modal Terminar Venta" close-button-aria-label="Close" aria-modal>
      <dialogo-agregar-cuenta-apartado :totalVenta="total" @close="onCerrar" @terminar="onTerminar"
        v-if="mostrarAgregarCuenta" :metodos="metodos" :choferes="choferes"
        :initialCliente="cliente" :initialDias="dias" :initialPagado="pagado" :initialDelivery="delivery"
        @actualizar="actualizar" :id="!!id"
        tipo="cuenta"></dialogo-agregar-cuenta-apartado>
    </b-modal>
    <comprobante-compra :venta="this.ventaRealizada" :tipo="tipoVenta" @impreso="onImpreso" v-if="mostrarComprobante"
      :porPagar="porPagar" :tamaño="tamaño" :enviarCliente="enviarCliente" />
  </section>
</template>
<script>
import BuscarProducto from '../Inventario/BuscarProducto.vue'
import TablaProductos from './TablaProductos.vue'
import DialogoAgregarCuentaApartado from './DialogoAgregarCuentaApartado'
import ComprobanteCompra from './ComprobanteCompra'
import MensajeInicial from '../Extras/MensajeInicial'
import AyudanteSesion from '../../Servicios/AyudanteSesion'
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'

export default {
  name: 'EditarCuenta',
  components: {
    BuscarProducto,
    TablaProductos,
    DialogoAgregarCuentaApartado,
    MensajeInicial,
    ComprobanteCompra
  },

  data: () => ({
    cargando: false,
    tamaño: 'tiquera',
    productos: [],
    choferes: [],
    metodos: [],
    total: 0,
    porPagar: 0,
    costoDelivery: null,
    esDelivery: false,
    deliveryGratis: false,
    mostrarDialogo: false,
    mostrarAgregarCuenta: false,
    ventaRealizada: null,
    mostrarComprobante: false,
    enviarCliente: false,
    tipoVenta: "",
    cliente: null,
    metodo: null,
    pagado: 0,
    delivery: null,
    id: null,
    dias: "",
    tipo: null,
  }),

  mounted() {
    this.cargando = true
    const payload = { accion: 'obtener' }

    this.tipo = this.$route.query.tipo

    Promise.all([
      HttpService.obtenerConConsultas('metodos.php', payload),
      HttpService.obtenerConConsultas('choferes.php', payload),
      HttpService.obtenerConConsultas('ventas.php', {
        accion: 'obtener_cuenta_apartado',
        id: this.$route.params.id,
        tipo: this.tipo
      })
    ]).then(([metodos, choferes, cuenta]) => {
      this.metodos = metodos
      this.choferes = choferes
      this.cargando = false
      this.productos = cuenta.productos
      this.total = cuenta.total
      this.cliente = cuenta.cliente
      this.metodo = cuenta.simple
      this.pagado = parseFloat(cuenta.pagado)
      this.id = cuenta.id,
      this.dias = cuenta.dias
      this.delivery = {
        costo: cuenta.delivery?.costo,
        destino: cuenta.direccionCliente,
        gratis: !!cuenta.delivery?.gratis,
        idChofer: cuenta.deliveryId
      }
    })
  },

  methods: {
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
    },

    onImpreso(resultado) {
      this.mostrarComprobante = resultado
    },

    actualizar(prop, valor) {
      this[prop] = valor
      this.calcularTotal()
    },

    onTerminar(venta) {
      this.calcularTotal()

      let total = this.total

      this.ventaRealizada = {
        total,
        productos: this.productos,
        cliente: venta.cliente.id,
        usuario: AyudanteSesion.usuario().id,
        nombreCliente: (venta.cliente.nombre) ? venta.cliente.nombre : 'MOSTRADOR',
        nombreUsuario: AyudanteSesion.usuario().usuario,
        telefonoCliente: venta.cliente.telefono,
        fecha: new Date().toJSON().slice(0, 10).replace(/-/g, '/')
      }

      let tipo = venta.tipo
      this.ventaRealizada.tipo = tipo
      this.ventaRealizada.pagado = venta.pagado
      this.ventaRealizada.simple = venta.simple
      this.ventaRealizada.origen = venta.origen
      this.ventaRealizada.idMetodo = venta.idMetodo
      this.ventaRealizada.delivery = venta.delivery
      this.ventaRealizada.chofer = venta.chofer
      this.ventaRealizada.pagado = venta.pagado
      this.ventaRealizada.simple = venta.simple
      this.ventaRealizada.origen = venta.origen
      this.ventaRealizada.idMetodo = venta.idMetodo
      this.ventaRealizada.delivery = venta.delivery
      this.ventaRealizada.chofer = venta.chofer
      this.ventaRealizada.dias = venta.dias

      if (tipo !== 'cotiza' && this.ventaRealizada.pagado === '') {
        this.ventaRealizada.pagado = 0
      }

      this.tipoVenta = venta.tipo

      this.cargando = true
      let datos = {
        accion: 'actualizar_cuenta',
        datos: this.ventaRealizada,
        id: this.id,
        tipo: this.tipo
      }

      this.costoDelivery = null
      this.esDelivery = false
      this.deliveryGratis = false

      HttpService.registrar('ventas.php', datos)
        .then(id => {
          if (!id) return

          this.productos = []
          this.total = 0
          this.cargando = false
          this.mostrarTerminarVenta = this.mostrarAgregarCuenta = this.mostrarAgregarApartado = this.mostrarRegistrarCotizacion = false
          this.mostrarDialogo = false
          this.$buefy.toast.open({
            type: 'is-info',
            message: tipo.toUpperCase() + ' registrado con éxito'
          })

          if (this.tipoVenta === 'cuenta' || this.tipoVenta === 'apartado') {
            return HttpService.obtenerConConsultas('ventas.php', {
              accion: 'por_pagar', id,
            })
          }
        }).then(porPagar => {
          if (porPagar) {
            this.porPagar = porPagar
          }

          this.$buefy.dialog.confirm({
            message: 'Selecciona el tamaño a imprimir',
            cancelText: 'Carta',
            confirmText: 'Tiquera',
            trapFocus: true,
            onConfirm: () => {
              this.tamaño = 'tiquera'
              this.confirmarEnvioCliente()
            },
            onCancel: () => {
              this.tamaño = 'carta'
              this.confirmarEnvioCliente()
            },
          })
        })

        this.$router.push(this.tipo === 'apartado' ? '/reporte-apartados' : '/reporte-cuentas')
    },

    confirmarEnvioCliente() {
      this.$buefy.dialog.confirm({
        message: '¿Enviar al cliente mediante WhatsApp?',
        cancelText: 'No',
        confirmText: 'Sí',
        trapFocus: true,
        onConfirm: () => {
          this.enviarCliente = true
          this.mostrarComprobante = true
        },
        onCancel: () => {
          this.enviarCliente = false
          this.mostrarComprobante = true
        },
      })
    },

    cancelarVenta() {
      this.$buefy.dialog.confirm({
        title: 'Cancelar venta',
        message: '¿Seguro que deseas cancelar la venta?',
        confirmText: 'Sí, cancelar',
        cancelText: 'No, continuar',
        type: 'is-danger',
        hasIcon: true,
        onConfirm: () => {
          this.$router.push('/realizar-venta')
        }
      })
    },


    abrirDialogo() {
      this.mostrarDialogo = true
      this.mostrarAgregarCuenta = true
    },

    onCerrar(opcion) {
      this.mostrarDialogo = false
      if (opcion === 'venta' || opcion === 'cuenta' || opcion === 'apartado' || opcion === 'cotiza')
        this.mostrarTerminarVenta = this.mostrarAgregarCuenta = this.mostrarAgregarApartado = this.mostrarRegistrarCotizacion = false
    },

    onQuitar(id) {
      let indice = this.productos.findIndex(producto => producto.id === id)
      this.productos.splice(indice, 1)
    },

    onAumentar(producto) {
      let verificaExistencia = this.verificarExistenciaAlcanzada(producto.existencia, producto.id)

      if (verificaExistencia) return

      if (producto.vendidoMayoreo) {
        this.verificarMayoreo(producto.cantidadMayoreo, producto.id, producto.precioMayoreo)
      }

      this.calcularTotal()
    },

    onSeleccionado(producto) {
      let verificaExistencia = this.verificarExistenciaAlcanzada(producto.existencia, producto.id)

      if (verificaExistencia) return

      if (producto.vendidoMayoreo) {
        this.verificarMayoreo(producto.cantidadMayoreo, producto.id, producto.precioMayoreo)
      }

      let existeEnLista = this.verificarSiEstaEnLista(producto.id)

      if (existeEnLista >= 0) {
        this.aumentarCantidad(existeEnLista)
        this.calcularTotal()
        return
      }

      this.agregarALista(producto)
      this.calcularTotal()
    },

    agregarALista(producto) {
      this.productos.push(
        {
          id: producto.id,
          codigo: producto.codigo,
          nombre: producto.nombre,
          precio: producto.precioVenta,
          precioVenta: producto.precioVenta,
          precioVenta2: producto.precioVenta2,
          precioVenta3: producto.precioVenta3,
          unidad: producto.unidad,
          cantidad: 1,
          existencia: producto.existencia,
          vendidoMayoreo: producto.vendidoMayoreo,
          cantidadMayoreo: producto.cantidadMayoreo,
          precioMayoreo: producto.precioMayoreo,
          mayoreoAplicado: false
        }
      )
    },

    verificarExistenciaAlcanzada(existencia, id) {
      let resultado = false
      this.productos.forEach(producto => {
        if (producto.id === id) {
          if (parseInt(producto.cantidad) >= parseInt(existencia)) {
            this.$buefy.toast.open({
              type: 'is-danger',
              message: 'El producto ' + producto.nombre + 'ha alcanzado la existencia máxima. Solo tienes ' + producto.existencia
            })
            producto.cantidad = existencia
            resultado = true
          }
        }

      })
      return resultado
    },

    verificarMayoreo(cantidadMayoreo, id, precioMayoreo) {
      this.productos.forEach(producto => {
        if (producto.id !== id) return

        if (producto.cantidad >= parseInt(cantidadMayoreo)) {
          if (producto.mayoreoAplicado) return
          this.$buefy.dialog.confirm({
            confirmText: 'Sí, aplicar',
            cancelText: 'No aplicar',
            message: 'El producto ' + producto.nombre + ' tiene mayoreo a partir de ' + cantidadMayoreo + ' piezas, ¿Desea aplicar el mayoreo?',
            onConfirm: () => {
              producto.precio = precioMayoreo
              producto.mayoreoAplicado = true
              this.$buefy.toast.open('Mayoreo aplicado correctamente a ' + producto.nombre)
            }
          })
        } else {
          producto.precio = producto.precioVenta
          producto.mayoreoAplicado = false
        }
      })
    },

    verificarSiEstaEnLista(id) {
      return this.productos.findIndex(producto => producto.id === id)
    },

    aumentarCantidad(indice) {
      let lista = this.productos
      let producto = lista[indice]
      producto.cantidad++
      this.productos = lista

    },

    calcularTotal() {
      let total = 0
      this.productos.forEach(producto => {
        total = parseFloat(producto.cantidad * producto.precio) + parseFloat(total)
      })

      const costo = parseFloat(this.costoDelivery)

      if (this.esDelivery && !this.deliveryGratis && !isNaN(costo)) {
        total += costo
      }

      this.total = total
    }
  }
}
</script>
