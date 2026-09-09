import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { renderToString } from '@vue/server-renderer'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createSSRApp, h } from 'vue'
import { MotionPlugin } from '@vueuse/motion'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

const pages = import.meta.glob('./Pages/**/*.vue')

createServer((page) =>
  createInertiaApp({
    page,

    render: renderToString,

    title: (title) => `${title} - ${appName}`,

    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, pages),

    setup({ App, props, plugin }) {
      const vueApp = createSSRApp({
        render: () => h(App, props),
      })

      /**
       * Inertia
       */
      vueApp.use(plugin)

      /**
       * VueUse Motion
       *
       * Required because the application uses
       * v-motion-* directives.
       */
      vueApp.use(MotionPlugin)

      /**
       * Ziggy
       */
      if (props?.initialPage?.props?.ziggy) {
        vueApp.use(ZiggyVue, {
          ...props.initialPage.props.ziggy,
          location: new URL(props.initialPage.props.ziggy.location),
        })
      }

      return vueApp
    },
  })
)
