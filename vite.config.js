import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  resolve: {
    alias: {
      'lucide-vue-next': '@lucide/vue',
    },
  },
  plugins: [
    laravel({
      input: ['resources/js/app.js', 'resources/js/landing.js', 'resources/css/landing.css'],
      ssr: 'resources/js/ssr.js',
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  build: {
    modulePreload: false, // Mencegah preloading seluruh file JS di halaman awal
  },
  // Make FullCalendar client-only (skip SSR)
  ssr: {
    noExternal: [/^@fullcalendar\/.*/],
  },
})