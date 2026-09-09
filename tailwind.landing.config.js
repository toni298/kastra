import defaultTheme from 'tailwindcss/defaultTheme'

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './resources/views/landing.blade.php',
    './resources/js/Pages/Welcome.vue',
    './resources/js/Pages/Landing/**/*.vue',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['ui-sans-serif', 'system-ui', ...defaultTheme.fontFamily.sans],
      },
      fontSize: {
        xs: ['0.8125rem', { lineHeight: '1.125rem' }],
        sm: ['0.9375rem', { lineHeight: '1.375rem' }],
        base: ['1.0625rem', { lineHeight: '1.625rem' }],
        lg: ['1.1875rem', { lineHeight: '1.8125rem' }],
        xl: ['1.3125rem', { lineHeight: '1.875rem' }],
        '2xl': ['1.5625rem', { lineHeight: '2.0625rem' }],
        '3xl': ['1.9375rem', { lineHeight: '2.375rem' }],
        '4xl': ['2.3125rem', { lineHeight: '2.625rem' }],
        '5xl': ['3.0625rem', { lineHeight: '1.05' }],
        '6xl': ['3.8125rem', { lineHeight: '1.05' }],
        '7xl': ['4.5625rem', { lineHeight: '1.05' }],
        '8xl': ['6.0625rem', { lineHeight: '1' }],
        '9xl': ['8.0625rem', { lineHeight: '1' }],
      },
    },
  },
  plugins: [],
}
