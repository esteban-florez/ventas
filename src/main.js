import Vue from 'vue'
import App from './App.vue'
import Buefy from 'buefy'
import 'buefy/dist/buefy.css'
import '@mdi/font/css/materialdesignicons.css'
import '@/assets/styles.css'
import router from './router'
import AyudanteSesion from './Servicios/AyudanteSesion'

Vue.use(Buefy)

Vue.mixin({
  methods: {
    can: AyudanteSesion.can(),
  },
})

Vue.config.productionTip = false

new Vue({
    router,
    render: h => h(App)
}).$mount('#app')
