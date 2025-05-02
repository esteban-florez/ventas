<template>
  <form @submit.prevent="terminarVenta">
    <div class="modal-card" style="width: 600px">
      <header class="modal-card-head has-background-success">
        <p class="modal-card-title has-text-white">Terminar venta</p>
        <button type="button" class="delete" @click="$emit('close', 'venta')" />
      </header>
      <section class="modal-card-body">
        <busqueda-cliente @seleccionado="onSeleccionado" :initialCliente="cliente.nombre ? cliente : null" />

        <!-- Pago Mixto -->
        <b-field class="my-2" label="Pago mixto">
          <b-switch v-model="pagoMixto" @input="resetMetodosPago"></b-switch>
        </b-field>

        <template v-if="!pagoMixto">
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
        </template>

        <section v-else>
          <div v-for="(metodo, index) in metodosPago" :key="index" class="mb-4">
            <div class="level">
              <div class="level-left">
                <h4 class="title is-6">Método {{ index + 1 }}</h4>
              </div>
              <div class="level-right">
                <button class="button is-danger is-small" @click="eliminarMetodoPago(index)" :disabled="metodosPago.length === 1">
                  Eliminar
                </button>
              </div>
            </div>

            <b-field label="Método">
              <b-select class="wide" placeholder="Seleccionar..." icon="tag-multiple" v-model="metodo.idMetodo" required>
                <option v-for="metodo in metodosSimples" :key="metodo" :value="metodo">
                  {{ metodo }}
                </option>
                <option v-for="metodo in metodos" :key="metodo.id" :value="metodo.id">
                  {{ metodo.nombre }}
                </option>
              </b-select>
            </b-field>

            <b-field label="Monto">
              <b-input type="number" step="0.01" v-model="metodo.monto" @input="validarMonto(index)" required>
              </b-input>
              <span v-if="metodo.monto">
                ({{ formatoMonto(metodo.monto) }})
              </span>
            </b-field>

            <b-field v-if="metodo.idMetodo && !esMetodoSimple(metodo.idMetodo)" label="Origen">
              <b-input v-model="metodo.origen" required></b-input>
            </b-field>
          </div>

          <b-button @click="agregarMetodoPago" :disabled="alcanzadoTotal" class="is-primary mb-4">
            Agregar método
          </b-button>
        </section>

        <b-field class="mt-3" label="El cliente paga con" v-if="!pagoMixto">
          <b-input step="any" icon="currency-usd" type="number" placeholder="Monto pagado" v-model="pagado" required>
          </b-input>
          <span v-if="pagado">
            ({{ formatoMonto(pagado) }})
          </span>
        </b-field>

        <div v-else class="notification is-light">
          <p class="has-text-weight-bold">Total pagado: ${{ formatoMonto(totalPagado) }}</p>
        </div>

        <!-- Deliveries -->
        <b-switch class="my-2" v-model="esDelivery" @input="manejarEsDelivery" type="is-info">
          ¿Añadir servicio de delivery?
        </b-switch>
        <div style="display: contents" v-if="esDelivery">
          <h4 class="is-size-4 has-text-weight-bold mt-5 has-text-centered">Datos del delivery</h4>
          <b-field class="mt-1" label="Costo del delivery">
            <b-input step="0.01" icon="currency-usd" type="number" placeholder="Costo del delivery" v-model="delivery.costo" @input="manejarCostoDelivery" required></b-input>
            <span v-if="delivery.costo">
              ({{ formatoMonto(delivery.costo) }})
            </span>
          </b-field> 
          <b-switch class="mb-3" v-model="delivery.gratis" type="is-info" @input="manejarDeliveryGratis">
            ¿Delivery gratis para el cliente?
          </b-switch>
          <b-field label="Destino del delivery">
            <b-input placeholder="Calle, casa, barrio, ciudad, estado" v-model="delivery.destino" required></b-input>
          </b-field>
          <b-field label="Chofer del delivery">
          <b-select
            class="wide"
            placeholder="Seleccionar..."
            icon="tag-multiple"
            v-model="delivery.idChofer"
            :multiple="true"
            required
          >
            <option value="0">Registrar nuevo chofer</option>
            <option v-for="chofer in choferes" :key="chofer.id" :value="chofer.id">
              {{ chofer.nombre }} ({{ chofer.tipo[0] }}-{{ chofer.ci }})
            </option>
          </b-select>
        </b-field>
        </div>
        <div v-if="esDelivery && delivery.idChofer && delivery.idChofer.includes('0')">
          <!-- Formulario de nuevo chofer -->
          <!-- ...campos del nuevo chofer... -->
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

        <p class="is-size-1 has-text-weight-bold">Total ${{ formatoMonto(totalVenta) }}</p>
        <p class="is-size-1 has-text-weight-bold" v-if="cambio > 0">
          Cambio ${{ formatoMonto(cambio) }}
        </p>
        <p class="is-size-1 has-text-weight-bold" v-else>
          Por pagar ${{ formatoMonto(totalVenta - totalPagado) }}
        </p>
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
import Utiles from '../../Servicios/Utiles'

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
      idMetodo: this.initialIdMetodo,
      origen: this.initialOrigen,
      cliente: {...this.initialCliente},
      esDelivery: !!(this.initialDelivery && (
        (Array.isArray(this.initialDelivery.idChofer) && this.initialDelivery.idChofer.length > 0) ||
        (typeof this.initialDelivery.idChofer === 'string' && this.initialDelivery.idChofer !== '') ||
        (typeof this.initialDelivery.idChofer === 'number' && this.initialDelivery.idChofer)
      )),
      nuevoChofer: false,
      delivery: {
        ...this.initialDelivery,
        idChofer: Array.isArray(this.initialDelivery?.idChofer)
          ? this.initialDelivery.idChofer
          : (this.initialDelivery?.idChofer !== null && this.initialDelivery?.idChofer !== undefined
              ? [this.initialDelivery.idChofer]
              : []),
        gratis: Boolean(this.initialDelivery?.gratis)
      },
      chofer: {...this.initialChofer},
      metodosSimples: Object.values(TIPOS_PAGO_SIMPLE),
      tipos: TIPOS_CLIENTE,
      // Pago Mixto
      pagoMixto: false,
      metodosPago: [{idMetodo: null, monto: 0, origen: '', simple: false}],
    }
  },

  methods: {
    formatoMonto(valor) {
      return Utiles.formatoMonto(valor)
    },
    onSeleccionado(cliente) {
      this.cliente = cliente
      this.delivery.destino = cliente.direccion
    },

    manejarEsDelivery() {
      this.$emit('actualizar', 'esDelivery', this.esDelivery)
    },

    manejarCostoDelivery() {
      this.$emit('actualizar', 'costoDelivery', this.delivery.costo)
    },

    manejarDeliveryGratis() {
      this.$emit('actualizar', 'deliveryGratis', this.delivery.gratis);
    },
    // Pago Mixto
    esMetodoSimple(metodo) {
      return this.metodosSimples.includes(metodo);
    },
    
    agregarMetodoPago() {
      if (!this.alcanzadoTotal) {
        this.metodosPago.push({idMetodo: null, monto: 0, origen: '', simple: false});
      }
    },
    
    eliminarMetodoPago(index) {
      this.metodosPago.splice(index, 1);
    },
    
    validarMonto(index) {
      const restante = this.totalVenta - this.totalPagado + parseFloat(this.metodosPago[index].monto);
      if (restante < 0) {
        this.metodosPago[index].monto = Math.max(0, this.metodosPago[index].monto + restante);
      }
    },
    
    resetMetodosPago() {
      this.metodosPago = [{idMetodo: null, monto: 0, origen: '', simple: false}];
    },
    
    terminarVenta() {
      if (this.totalPagado < this.totalVenta) {
        this.$buefy.toast.open({
          type: 'is-danger',
          message: 'El pago no cubre el total de la venta'
        });
        return;
      }

      const pagos = this.pagoMixto 
        ? this.metodosPago.map(metodo => ({
            idMetodo: metodo.idMetodo,
            monto: parseFloat(metodo.monto),
            origen: metodo.origen,
            simple: this.metodosSimples.includes(metodo.idMetodo)
          }))
        : [{
            idMetodo: this.idMetodo,
            monto: parseFloat(this.pagado),
            origen: this.origen,
            simple: this.esSimple
          }];

      let payload = {
        tipo: 'venta',
        pagado: this.totalPagado,
        cambio: this.cambio,
        cliente: this.cliente,
        pagos: pagos,
        pagoMixto: this.pagoMixto
      };

      if (this.esDelivery) {
        payload.delivery = this.delivery;
        payload.chofer = this.chofer;
      }

      this.$emit('terminar', payload);
    }
  },

  computed: {
    esSimple() {
      if (this.pagoMixto) return false;
      return this.metodosSimples.includes(this.idMetodo);
    },
    
    totalPagado() {
      if (this.pagoMixto) {
        return this.metodosPago.reduce((sum, metodo) => sum + parseFloat(metodo.monto || 0), 0);
      }
      return parseFloat(this.pagado || 0);
    },
    
    alcanzadoTotal() {
      return this.totalPagado >= this.totalVenta;
    },
    
    cambio() {
      if (this.totalPagado > this.totalVenta) {
        return this.totalPagado - this.totalVenta;
      }
      return 0;
    }
  }
}
</script>

<style scoped>
.metodo-pago-item {
  border: 1px solid #eee;
  padding: 1rem;
  margin-bottom: 1rem;
  border-radius: 4px;
  background-color: #f9f9f9;
}

.title.is-6 {
  margin-bottom: 0.5rem;
}
</style>