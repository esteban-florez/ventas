<template>
  <form @submit.prevent="submit">
    <b-field label="Nombre">
      <b-input name="name" type="text" placeholder="Ej. Pago Móvil Personal" required v-model="campos.nombre"></b-input>
    </b-field>
    <b-field label="Plataforma" v-if="!editar">
      <b-select name="tipo" class="wide" required v-model="campos.tipo">
        <option value="" disabled>Seleccionar...</option>
        <option v-for="tipo in TIPOS_PAGO" :key="tipo" :value="tipo">
          {{ tipo }}
        </option>
      </b-select>
    </b-field>
    <b-field label="Número de cuenta" v-if="esTransferencia">
      <b-input name="cuenta" step="1" type="number" placeholder="Ej. 12049209401294024814"
      required v-model="campos.cuenta"></b-input>
    </b-field>
    <b-field label="Cédula/RIF" v-if="esBanco">
      <div class="columns">
        <b-select name="tipoCi" class="column is-one-fifth wide" required v-model="campos.tipoCi">
          <option v-for="tipo in Object.values(TIPOS_CLIENTE)" :key="tipo" :value="tipo">
            {{ tipo }}
          </option>
        </b-select>
        <b-input class="column" type="number" placeholder="Ej. 30000000" required v-model="campos.ci"></b-input>
      </div>
    </b-field>
    <b-field label="Banco" v-if="esBanco">
      <b-select name="banco" class="wide" required v-model="campos.banco">
        <option value="" disabled>Seleccionar...</option>
        <option v-for="banco in BANCOS" :key="banco" :value="banco">
          {{ banco }}
        </option>
      </b-select>
    </b-field>
    <b-field label="Teléfono" v-if="esPagoMovil">
      <b-input name="telefono" step="any" type="number" placeholder="Ej. 2311459874"
        required v-model="campos.telefono"></b-input>
    </b-field>
    <b-field label="Beneficiario" v-if="esTransferencia">
      <b-input name="beneficiario" type="text" placeholder="Ej. Luis Pérez" required v-model="campos.beneficiario"></b-input>
    </b-field>
    <b-field label="Correo Electrónico" v-if="esCorreo">
      <b-input name="email" type="email" placeholder="Ej. correo@ejemplo.com" required v-model="campos.correo"></b-input>
    </b-field>
    <div class="buttons has-text-centered">
      <b-button native-type="submit" type="is-primary" size="is-large" icon-left="check">Registrar</b-button>
      <b-button type="is-dark" size="is-large" icon-left="cancel" tag="router-link" to="/metodos">Cancelar</b-button>
    </div>
    <errores-component :errores="mensajesError" v-if="mensajesError.length > 0" />
  </form>
</template>

<script>
import ErroresComponent from '../Extras/ErroresComponent'
import HttpService from '../../Servicios/HttpService'
import { TIPOS_PAGO_CRUD, TIPOS_CLIENTE, BANCOS, TIPOS_PAGO } from '@/consts'

const camposIniciales = {
  nombre: '',
  tipo: '',
  banco: '',
  cuenta: '',
  tipoCi: 'Venezolano',
  ci: '',
  beneficiario: '',
  telefono: '',
  correo: '',
}

export default {
  name: 'FormUsuario',
  props: {
    editar: [Boolean, undefined],
  },
  components: { ErroresComponent },

  data: () => ({
    campos: structuredClone(camposIniciales),
    TIPOS_PAGO: TIPOS_PAGO_CRUD,
    TIPOS_CLIENTE,
    BANCOS,
    mensajesError: []
  }),

  async mounted() {
    if (this.editar === true) {
      this.$emit('cargando', true)
      this.campos = await HttpService.obtenerConConsultas('metodos.php', {
        accion: 'obtener_por_id',
        id: this.$route.params.id
      })
      this.$emit('cargando', false)
    }
  },

  computed: {
    esBanco: function () {
      return this.esPagoMovil || this.esTransferencia
    },
    esCorreo: function () {
      const { zelle, binance } = TIPOS_PAGO
      const { tipo } = this.campos
      return tipo === zelle || tipo === binance
    },
    esTransferencia: function () {
      return this.campos.tipo === TIPOS_PAGO.transferencia
    },
    esPagoMovil: function () {
      return this.campos.tipo === TIPOS_PAGO.pagomovil
    },
  },

  methods: {
    submit() {
      this.$emit('submit', this.campos)
      this.campos = structuredClone(camposIniciales)
    }
  }
}
</script>
