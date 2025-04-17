<template>
  <section>
    <p class="title is-1">Realizar abono</p>
    <b-breadcrumb align="is-left">
      <b-breadcrumb-item tag='router-link' to="/">Inicio</b-breadcrumb-item>
      <b-breadcrumb-item tag='router-link' :to="{ name: 'AbonosComponent', params: { id: this.$route.params.id } }">Abonos</b-breadcrumb-item>
      <b-breadcrumb-item active>Realizar abono</b-breadcrumb-item>
    </b-breadcrumb>
    <p class="is-size-5 has-text-weight-semibold">
      Deuda total: ${{ porPagar.toFixed(2) }}
    </p>
    <hr class="my-2">
    <b-switch class="mb-3" v-model="liquidar" @input="fijarMonto">
      Liquidar deuda total
    </b-switch>
    <form @submit.prevent="onRegistrar">
      <b-field label="Monto a abonar">
        <b-input type="number" step="0.01" v-model="monto" placeholder="Introduce el monto a abonar..." icon="currency-usd" :max="porPagar" :readonly="liquidar" required></b-input>
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
        <b-input type="text" name="origen" v-model="origen" placeholder="Origen del pago" icon="account" required></b-input>
      </b-field>
      <hr class="mb-2 mt-4">
      <p class="is-size-5 has-text-weight-semibold">
        Restante después del abono: ${{ restante.toFixed(2) }}
      </p>
      <div class="buttons has-text-centered mt-5">
        <b-button native-type="submit" type="is-primary" icon-left="check">Registrar</b-button>
        <b-button type="is-dark" icon-left="cancel" tag="router-link" :to="{ name: 'AbonosComponent', params: { id: this.$route.params.id } }">Cancelar</b-button>
      </div>
    </form>
    <b-loading :is-full-page="true" v-model="cargando" :can-cancel="false"></b-loading>
  </section>
</template>
<script>
import { TIPOS_PAGO_SIMPLE } from '@/consts'
import HttpService from '../../Servicios/HttpService'

export default {
  name: 'RealizarAbono',

  data: () => ({
    cargando: false,
    metodos: [],
    porPagar: null,
    origen: null,
    simple: null,
    monto: null,
    idMetodo: null,
    metodosSimples: Object.values(TIPOS_PAGO_SIMPLE),
    liquidar: false,
  }),

  mounted() {
    this.obtenerDatos()
  },

  methods: {
    fijarMonto() {
      if (this.liquidar) {
        this.monto = this.porPagar.toFixed(2)
      } else {
        this.monto = null
      }
    },

    async obtenerDatos() {
      this.cargando = true

      const porPagarPromise = HttpService.obtenerConConsultas('ventas.php', {
        accion: 'por_pagar',
        id: this.$route.params.id,
      })

      const metodosPromise = HttpService.obtenerConConsultas('metodos.php', {
        accion: 'obtener'
      })

      const [porPagar, metodos] = await Promise.all([
        porPagarPromise, metodosPromise
      ])

      this.porPagar = Number(porPagar)
      this.metodos = metodos
      this.cargando = false
    },

    async onRegistrar() {
      this.cargando = true
      const payload = {
        accion: 'realizar_abono',
        abono: {
          monto: this.monto,
          idCuenta: this.$route.params.id,
        },
      }

      if (this.esSimple) {
        payload.abono.simple = this.idMetodo
      } else {
        payload.abono.idMetodo = this.idMetodo
        payload.abono.origen = this.origen
      }

      const registrado = await HttpService.registrar('ventas.php', payload)
      // TODO -> imprimir comprobante de abono
      if (!registrado) return

      this.cargando = false

      this.$router.push({
        name: 'AbonosComponent',
      })

      this.$buefy.toast.open({
        type: 'is-info',
        message: 'Abono registrado con éxito.'
      })
    },
  },

  computed: {
    esSimple() {
      return this.metodosSimples.includes(this.idMetodo)
    },
    restante() {
      return this.porPagar - Number(this.monto)
    }
  }
}
</script>

<style>
  input[readonly].input {
    background-color: whitesmoke;
    cursor: default;
    pointer-events: none;
    user-select: none;
  }
</style>
