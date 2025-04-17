<template>
  <form @submit.prevent="terminarVenta">
    <div class="modal-card" style="width: 600px">
      <header class="modal-card-head has-background-success">
        <p class="modal-card-title has-text-white">Terminar venta</p>
        <button type="button" class="delete" @click="$emit('close', 'venta')" />
      </header>
      <section class="modal-card-body">
        <busqueda-cliente @seleccionado="onSeleccionado" :initialCliente="cliente.nombre ? cliente : null" />
        <b-field class="mt-3" label="Método de pago">
          <b-select class="wide" placeholder="Seleccionar..." icon="tag-multiple" v-model="idMetodo" required>
            <option v-for="metodo in metodosSimples" :key="metodo" :value="metodo">
              {{ metodo }}
            </option>
            <option v-for="metodo in metodos" :key="metodo.id" :value="metodo.id">
              {{ metodo.nombre }}
            </option>
          </b-select>
        </b-field>
        <b-field label="Origen" v-if="!esSimple && idMetodo">
          <b-input type="text" name="origen" v-model="origen" placeholder="Origen del pago" required></b-input>
        </b-field>
        <b-switch v-model="esDelivery" @input="manejarEsDelivery" type="is-info">
          ¿Añadir servicio de delivery?
        </b-switch>
        <div style="display: contents" v-if="esDelivery">
          <h4 class="is-size-4 has-text-weight-bold mt-5 has-text-centered">Datos del delivery</h4>
          <b-field class="mt-1" label="Costo del delivery">
            <b-input step="0.01" icon="currency-usd" type="number" placeholder="Costo del delivery" v-model="delivery.costo" @input="manejarCostoDelivery" required></b-input>
          </b-field>
          <b-switch class="mb-3" v-model="delivery.gratis" type="is-info" @input="$emit('actualizar', 'deliveryGratis', delivery.gratis)">
            ¿Delivery gratis para el cliente?
          </b-switch>
          <b-field label="Destino del delivery">
            <b-input placeholder="Calle, casa, barrio, ciudad, estado" v-model="delivery.destino" required></b-input>
          </b-field>
          <b-field label="Chofer del delivery">
            <b-select class="wide" placeholder="Seleccionar..." icon="tag-multiple" v-model="delivery.idChofer" required>
              <option value="0">Registrar nuevo chofer</option>
              <option v-for="chofer in choferes" :key="chofer.id" :value="chofer.id">
                {{ chofer.nombre }} ({{ chofer.tipo[0] }}-{{ chofer.ci }})
              </option>
            </b-select>
          </b-field>
        </div>
        <div style="display: contents;" v-if="esDelivery && delivery.idChofer === '0' ">
          <h4 class="is-size-4 has-text-weight-bold mt-5 has-text-centered">Datos del chofer</h4>
          <b-field class="mt-1" label="Nombre del chofer">
            <b-input step="any" icon="account" type="text" placeholder="Ej. Don Paco" v-model="chofer.nombre" required></b-input>
          </b-field>
          <b-field label="Teléfono del chofer">
            <b-input step="any" icon="phone" type="number" placeholder="Ej. 2311459874" v-model="chofer.telefono" required></b-input>
          </b-field>
          <b-field label="Tipo de identidad">
            <b-select class="wide" placeholder="Seleccionar..." icon="tag-multiple" v-model="chofer.tipo" required>
              <option v-for="tipo in tipos" :key="tipo" :value="tipo">
                {{ tipo }}
              </option>
            </b-select>
          </b-field>
          <b-field label="Cédula/RIF">
            <b-input type="number" placeholder="Ej. 30000000" v-model="chofer.ci" required></b-input>
          </b-field>
        </div>
        <b-field class="mt-3" label="El cliente paga con">
          <b-input step="any" icon="currency-usd" type="number" placeholder="Monto pagado" v-model="pagado" @input="calcularCambio" required></b-input>
        </b-field>
        <p class="is-size-1 has-text-weight-bold">Total ${{ totalVenta }}</p>
        <p class="is-size-1 has-text-weight-bold">Cambio ${{ cambio }}</p>
      </section>
      <footer class="modal-card-foot">
        <b-button label="Cancelar" icon-left="cancel" size="is-medium" @click="$emit('close', 'venta')" />
        <b-button label="Terminar venta" type="is-success" icon-left="check" size="is-medium" native-type="submit" />
      </footer>
    </div>
  </form>
</template>
<script>
import BusquedaCliente from '../Clientes/BusquedaCliente'
import { TIPOS_PAGO_SIMPLE, TIPOS_CLIENTE } from '@/consts'

export default {
  name: 'DialogoTerminarVenta',
  props: {
    totalVenta: {
      type: [String, Number],
      default: 0
    },
    metodos: {
      type: Array,
      default: () => []
    },
    choferes: {
      type: Array,
      default: () => []
    },
    initialPagado: {
      type: [String, Number],
      default: ''
    },
    initialIdMetodo: {
      type: [String, Number],
      default: null
    },
    initialOrigen: {
      type: String,
      default: ''
    },
    initialCliente: {
      type: Object,
      default: () => ({})
    },
    initialDelivery: {
      type: Object,
      default: () => ({
        costo: null,
        destino: null,
        gratis: false,
        idChofer: null
      })
    },
    initialChofer: {
      type: Object,
      default: () => ({
        nombre: null,
        ci: null,
        tipo: null,
        telefono: null
      })
    }
  },
  components: { BusquedaCliente },

  data() {
    return {
      pagado: this.initialPagado,
      cambio: (this.initialPagado - this.totalVenta) ?? 0,
      idMetodo: this.initialIdMetodo,
      origen: this.initialOrigen,
      cliente: {...this.initialCliente},
      esDelivery: this.initialDelivery,
      nuevoChofer: false,
      delivery: {...this.initialDelivery},
      chofer: {...this.initialChofer},
      metodosSimples: Object.values(TIPOS_PAGO_SIMPLE),
      tipos: TIPOS_CLIENTE
    }
  },

  methods: {
    onSeleccionado(cliente) {
      this.cliente = cliente
    },

    manejarEsDelivery() {
      this.$emit('actualizar', 'esDelivery', this.esDelivery)
    },

    manejarCostoDelivery() {
      this.$emit('actualizar', 'costoDelivery', this.delivery.costo)
    },

    calcularCambio() {
      this.cambio = parseFloat(this.pagado - this.totalVenta)
    },

    terminarVenta() {
      if (this.pagado === '' || this.pagado < this.totalVenta) {
        this.$buefy.toast.open({
          type: 'is-danger',
          message: 'Debes colocar el total pagado.'
        })
        return
      }

      let payload = {
        tipo: 'venta',
        pagado: this.pagado,
        cambio: this.cambio,
        cliente: this.cliente,
      }

      if (this.esDelivery) {
        payload.delivery = this.delivery
        payload.chofer = this.chofer
      }

      if (this.esSimple) {
        payload.simple = this.idMetodo
      } else {
        payload.idMetodo = this.idMetodo
        payload.origen = this.origen
      }

      this.$emit('terminar', payload)
    }
  },

  computed: {
    esSimple: function () {
      return this.metodosSimples.includes(this.idMetodo)
    }
  }
}
</script>
