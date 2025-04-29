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
          Total ${{formatoMonto(total.toFixed(2)) }}
        </p>
        <nav class="level mt-2">
          <div class="level-item has-text-centered" v-if="can('ventas.registrar_venta')">
            <b-button class="button is-responsive" type="is-success" inverted icon-left="check" size="is-large"
              @click="abrirDialogo('venta')">
              Terminar venta
            </b-button>
          </div>
          <div class="level-item has-text-centered" v-if="can('ventas.registrar_cuenta')">
            <b-button class="button is-responsive" type="is-info" inverted icon-left="wallet-plus" size="is-large"
              @click="abrirDialogo('cuenta')">
              Agregar a cuenta
            </b-button>
          </div>
          <div class="level-item has-text-centered" v-if="can('ventas.registrar_apartado')">
            <b-button class="button is-responsive" type="is-dark" inverted icon-left="wallet-travel" size="is-large"
              @click="abrirDialogo('apartado')">
              Realizar apartado
            </b-button>
          </div>
          <div class="level-item has-text-centered">
            <b-button class="button is-responsive" type="is-danger" inverted icon-left="cancel" size="is-large"
              @click="cancelarVenta">
              Cancelar
            </b-button>
          </div>
          <div class="level-item has-text-centered" v-if="can('ventas.registrar_cotiza')">
            <b-button class="button is-responsive" type="is-warning" inverted icon-left="ticket-outline" size="is-large"
              @click="abrirDialogo('cotiza')">
              Cotizar
            </b-button>
          </div>
        </nav>
      </div>
    </div>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
    <b-modal v-model="mostrarDialogo" has-modal-card trap-focus :destroy-on-hide="false" aria-role="dialog"
      aria-label="Modal Terminar Venta" close-button-aria-label="Close" aria-modal>
      <dialogo-terminar-venta :totalVenta="total" @close="onCerrar" @terminar="onTerminar" v-if="mostrarTerminarVenta"
        :metodos="metodos" :choferes="choferes" @actualizar="actualizar"></dialogo-terminar-venta>
      <dialogo-agregar-cuenta-apartado :totalVenta="total" @close="onCerrar" @terminar="onTerminar"
        v-if="mostrarAgregarCuenta" :metodos="metodos" :choferes="choferes" @actualizar="actualizar"
        tipo="cuenta"></dialogo-agregar-cuenta-apartado>
      <dialogo-agregar-cuenta-apartado :totalVenta="total" @close="onCerrar" @terminar="onTerminar"
        v-if="mostrarAgregarApartado" :metodos="metodos" :choferes="choferes" @actualizar="actualizar"
        tipo="apartado"></dialogo-agregar-cuenta-apartado>
      <dialogo-cotizar :totalVenta="total" @close="onCerrar" @terminar="onTerminar"
        v-if="mostrarRegistrarCotizacion"></dialogo-cotizar>
    </b-modal>
    <comprobante-compra :venta="this.ventaRealizada" :tipo="tipoVenta" @impreso="onImpreso" v-if="mostrarComprobante"
      :porPagar="porPagar" :tamaño="tamaño" :enviarCliente="enviarCliente" />
  </section>
</template>
<script>
import BuscarProducto from '../Inventario/BuscarProducto.vue'
import TablaProductos from './TablaProductos.vue'
import DialogoTerminarVenta from './DialogoTerminarVenta'
import DialogoAgregarCuentaApartado from './DialogoAgregarCuentaApartado.vue'
import DialogoCotizar from './DialogoCotizar'
import ComprobanteCompra from './ComprobanteCompra'
import MensajeInicial from '../Extras/MensajeInicial'
import AyudanteSesion from '../../Servicios/AyudanteSesion'
import HttpService from '../../Servicios/HttpService'
import Utiles from '../../Servicios/Utiles'

export default {
  name: 'RealizarVenta',
  components: {
    BuscarProducto,
    TablaProductos,
    DialogoTerminarVenta,
    DialogoAgregarCuentaApartado,
    DialogoCotizar,
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
    mostrarTerminarVenta: false,
    mostrarAgregarCuenta: false,
    mostrarAgregarApartado: false,
    mostrarRegistrarCotizacion: false,
    ventaRealizada: null,
    mostrarComprobante: false,
    enviarCliente: false,
    tipoVenta: ""
  }),

  mounted() {
    this.cargando = true
    const payload = { accion: 'obtener' }

    Promise.all([
      HttpService.obtenerConConsultas('metodos.php', payload),
      HttpService.obtenerConConsultas('choferes.php', payload)
    ]).then(([metodos, choferes]) => {
      this.metodos = metodos
      this.choferes = choferes
      this.cargando = false
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
        direccionCliente: venta.delivery?.destino ?? venta.cliente.direccion,
        fecha: new Date().toJSON().slice(0, 10).replace(/-/g, '/')
      }

      let tipo = venta.tipo
      this.ventaRealizada.tipo = tipo

      switch (tipo) {
        case 'venta':
          this.ventaRealizada.pagado = venta.pagado
          this.ventaRealizada.simple = venta.simple
          this.ventaRealizada.origen = venta.origen
          this.ventaRealizada.idMetodo = venta.idMetodo
          this.ventaRealizada.delivery = venta.delivery
          this.ventaRealizada.chofer = venta.chofer
          break

        case 'cotiza':
          this.ventaRealizada.hasta = venta.hasta
          break

        default:
          this.ventaRealizada.pagado = venta.pagado
          this.ventaRealizada.simple = venta.simple
          this.ventaRealizada.origen = venta.origen
          this.ventaRealizada.idMetodo = venta.idMetodo
          this.ventaRealizada.delivery = venta.delivery
          this.ventaRealizada.chofer = venta.chofer
          this.ventaRealizada.dias = venta.dias
          break
      }

      if (tipo !== 'cotiza' && this.ventaRealizada.pagado === '') {
        this.ventaRealizada.pagado = 0
      }

      this.tipoVenta = venta.tipo

      this.cargando = true
      let datos = {
        accion: 'registrar_' + tipo,
        datos: this.ventaRealizada
      }

      this.costoDelivery = null
      this.esDelivery = false
      this.deliveryGratis = false

      HttpService.registrar('ventas.php', datos)
        .then(id => {
          if (!id) {
            this.cargando = false
            this.$buefy.toast.open({
              type: 'is-danger',
              message: 'Error al registrar la venta. Verifica los datos.'
            });
            return;
          }

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
          this.productos = []
          this.total = 0
          this.$buefy.toast.open('Venta cancelada')
        }
      })
    },


    abrirDialogo(opcion) {
      this.mostrarTerminarVenta = false;
      this.mostrarAgregarCuenta = false;
      this.mostrarAgregarApartado = false;
      this.mostrarRegistrarCotizacion = false;
      
      this.$nextTick(() => {
        this.mostrarDialogo = true;
        switch (opcion) {
          case "venta":
            this.mostrarTerminarVenta = true;
            break;
          case "cuenta":
            this.mostrarAgregarCuenta = true;
            break;
          case "apartado":
            this.mostrarAgregarApartado = true;
            break;
          case "cotiza":
            this.mostrarRegistrarCotizacion = true;
            break;
        }
      });
    },

    onCerrar(opcion) {
      this.mostrarDialogo = false
      if (opcion === 'venta' || opcion === 'cuenta' || opcion === 'apartado' || opcion === 'cotiza') {
        this.mostrarTerminarVenta = this.mostrarAgregarCuenta = this.mostrarAgregarApartado = this.mostrarRegistrarCotizacion = false
      }
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

              this.calcularTotal()
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
    },
  },

  watch: {
    mostrarDialogo(newValue) {
      if (!newValue) {
        this.costoDelivery = 0
        this.deliveryGratis = false
        this.esDelivery = false
        this.calcularTotal()
      }
    }
  }
}
</script>
