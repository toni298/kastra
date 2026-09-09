const element = document.getElementById('app')
const page = JSON.parse(element.dataset.page)

const setMenuState = (open) => {
  const button = document.querySelector('[data-menu-toggle]')
  const menu = document.getElementById('landing-mobile-menu')
  const openIcon = document.querySelector('[data-menu-open-icon]')
  const closeIcon = document.querySelector('[data-menu-close-icon]')

  if (!button || !menu || !openIcon || !closeIcon) return

  button.setAttribute('aria-expanded', String(open))
  button.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu')
  menu.classList.toggle('hidden', !open)
  openIcon.classList.toggle('hidden', open)
  closeIcon.classList.toggle('hidden', !open)
}

const syncThemeToggleLabels = (useDarkTheme) => {
  document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.setAttribute('aria-label', useDarkTheme ? 'Aktifkan tema terang' : 'Aktifkan tema gelap')
  })
}

const setupLandingInteractions = () => {
  document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => {
    const menu = document.getElementById('landing-mobile-menu')
    setMenuState(menu?.classList.contains('hidden') ?? true)
  })

  document.querySelectorAll('[data-menu-link]').forEach((link) => {
    link.addEventListener('click', () => setMenuState(false))
  })

  syncThemeToggleLabels(document.documentElement.classList.contains('dark'))

  document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const useDarkTheme = !document.documentElement.classList.contains('dark')
      document.documentElement.classList.toggle('dark', useDarkTheme)
      localStorage.setItem('kastra-theme', useDarkTheme ? 'dark' : 'light')
      syncThemeToggleLabels(useDarkTheme)
    })
  })
}

Promise.all([import('vue'), import('@vueuse/motion'), import('./Pages/Welcome.vue')]).then(
  ([vue, motion, pageModule]) => {
    const createVueApp = element.hasChildNodes() ? vue.createSSRApp : vue.createApp

    createVueApp(pageModule.default, page.props).use(motion.MotionPlugin).mount(element)

    setupLandingInteractions()
  }
)
