<template>
  <form @submit.prevent="terminarVenta">
    <div class="modal-card" style="width: 600px">
      <header class="modal-card-head has-background-success">
        <p class="modal-card-title has-text-white">Terminar venta</p>
        <button type="button" class="delete" @click="$emit('close', 'venta')" />
      </header>
      <section class="modal-card-body">
        <p class="is-size-1 has-text-weight-bold">Total ${{ totalVenta }}</p>
        <busqueda-cliente @seleccionado="onSeleccionado" />
        <b-field label="El cliente paga con">
          <b-input step="any" ref="pagado" icon="currency-usd" type="number" placeholder="Monto pagado" v-model="pagado" @input="calcularCambio" @keyup.enter.native="terminarVenta" size="is-medium"></b-input>
        </b-field>
        <b-field label="Método de pago">
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
import { TIPOS_PAGO_SIMPLE } from '@/consts'

export default {
  name: 'DialogoTerminarVenta',
  props: ['totalVenta', 'metodos'],
  components: { BusquedaCliente },

  data: () => ({
    pagado: '',
    cambio: 0,
    idMetodo: null,
    origen: '',
    cliente: {},
    metodosSimples: Object.values(TIPOS_PAGO_SIMPLE),
  }),

  mounted() {
    this.$refs.pagado.focus()
  },

  methods: {
    onSeleccionado(cliente) {
      this.cliente = cliente
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

      if (this.esSimple) {
        payload.simple = this.idMetodo
      } else {
        payload.idMetodo = this.idMetodo
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
