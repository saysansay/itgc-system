import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './assets/main.css'
import Select2 from './components/Select2.vue'
import FlexDate from './components/FlexDate.vue'

// Bootstrap
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'vue-select/dist/vue-select.css'
import 'flatpickr/dist/flatpickr.css'

const app = createApp(App)

app.use(router)
app.component('Select2', Select2)
app.component('FlexDate', FlexDate)
app.mount('#app')
