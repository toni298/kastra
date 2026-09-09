import { computed, onMounted, ref } from 'vue'

const storageKey = 'kastra-theme'
const currentTheme = ref('light')

const applyTheme = (theme) => {
  if (typeof document === 'undefined') return

  document.documentElement.classList.toggle('dark', theme === 'dark')
}

export function useTheme() {
  const isDark = computed(() => currentTheme.value === 'dark')

  const setTheme = (theme) => {
    currentTheme.value = theme
    applyTheme(theme)

    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(storageKey, theme)
    }
  }

  const toggleTheme = () => setTheme(isDark.value ? 'light' : 'dark')

  onMounted(() => {
    const savedTheme = localStorage.getItem(storageKey)
    setTheme(savedTheme === 'dark' ? 'dark' : 'light')
  })

  return { isDark, setTheme, toggleTheme }
}
