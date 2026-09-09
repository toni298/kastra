import '../css/app.css'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, Fragment, h } from 'vue'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  async setup({ el, App, props, plugin }) {
    const isLanding = props.initialPage.component === 'Welcome'
    let ToastNotifications = null
    let runtime = null

    if (!isLanding) {
      const [motion, toast, axiosModule, , toastNotifications] = await Promise.all([
        import('@vueuse/motion'),
        import('vue-toastification'),
        import('axios'),
        import('vue-toastification/dist/index.css'),
        import('./Components/Feedback/ToastNotifications.vue'),
      ])

      ToastNotifications = toastNotifications.default
      runtime = { motion, toast, axios: axiosModule.default }
    }

    const vueApp = createApp({
      render: () =>
        h(Fragment, [h(App, props), ...(ToastNotifications ? [h(ToastNotifications)] : [])]),
    }).use(plugin)

    if (runtime) {
      const { MotionPlugin } = runtime.motion
      const { default: Toast, POSITION } = runtime.toast

      window.axios = runtime.axios
      window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

      vueApp.use(MotionPlugin).use(Toast, {
        position: POSITION.TOP_RIGHT,
        timeout: 4500,
        closeOnClick: true,
        pauseOnFocusLoss: true,
        pauseOnHover: true,
        draggable: true,
        hideProgressBar: false,
      })
    }

    return vueApp.mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})