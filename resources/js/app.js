import '../css/app.css'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import { MotionPlugin } from '@vueuse/motion'
import { ZiggyVue } from 'ziggy-js'
import Toast, { POSITION } from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import axios from 'axios'

// Konfigurasi Axios Global
window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,

  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue'),
    ),

  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(MotionPlugin)
      .use(ZiggyVue)
      .use(Toast, {
        position: POSITION.TOP_RIGHT,
        timeout: 4500,
        closeOnClick: true,
        pauseOnFocusLoss: true,
        pauseOnHover: true,
        draggable: true,
        hideProgressBar: false,
      })
      .mount(el)
  },

  progress: {
    color: '#4B5563',
  },
})