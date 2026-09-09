// Directive motion scroll-reveal untuk storefront.
// Pemakaian: v-reveal="'fade-up'" atau v-reveal="{ type: 'slide-left', delay: 100 }"
// Karakter tersedia: fade-up | fade-down | slide-left | slide-right | zoom | blur | flip

import { onBeforeUnmount } from 'vue'

const STYLES = `
  .reveal-init {
    opacity: 0;
    transition-property: opacity, transform, filter;
    transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
    will-change: opacity, transform, filter;
  }
  .reveal-init[data-type="fade-up"]    { transform: translateY(40px); }
  .reveal-init[data-type="fade-down"]  { transform: translateY(-40px); }
  .reveal-init[data-type="slide-left"] { transform: translateX(60px); }
  .reveal-init[data-type="slide-right"]{ transform: translateX(-60px); }
  .reveal-init[data-type="zoom"]       { transform: scale(0.9); }
  .reveal-init[data-type="blur"]       { filter: blur(12px); transform: translateY(20px); }
  .reveal-init[data-type="flip"]       { transform: perspective(600px) rotateX(-18deg); transform-origin: top; }

  .reveal-in {
    opacity: 1 !important;
    transform: none !important;
    filter: none !important;
  }
`

let styleInjected = false
function injectStyle() {
  if (styleInjected || typeof document === 'undefined') return
  const tag = document.createElement('style')
  tag.id = 'store-reveal-styles'
  tag.textContent = STYLES
  document.head.appendChild(tag)
  styleInjected = true
}

function setup(el, binding) {
  injectStyle()
  const opts = typeof binding.value === 'string' ? { type: binding.value } : (binding.value || {})
  const type = opts.type || 'fade-up'
  const delay = opts.delay || 0
  const duration = opts.duration || 700
  const threshold = opts.threshold ?? 0.15
  const once = opts.once !== false

  el.classList.add('reveal-init')
  el.dataset.type = type
  el.style.transitionDuration = `${duration}ms`
  if (delay) el.style.transitionDelay = `${delay}ms`

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('reveal-in')
        if (once) observer.unobserve(entry.target)
      } else if (!once) {
        entry.target.classList.remove('reveal-in')
      }
    })
  }, { threshold })

  observer.observe(el)
  el._revealObserver = observer
}

function cleanup(el) {
  if (el._revealObserver) {
    el._revealObserver.disconnect()
    delete el._revealObserver
  }
}

export const vReveal = {
  mounted: setup,
  updated: (el, binding) => {
    // Re-setup jika type/delay berubah
    if (JSON.stringify(binding.value) !== JSON.stringify(binding.oldValue)) {
      cleanup(el)
      el.classList.remove('reveal-in', 'reveal-init')
      setup(el, binding)
    }
  },
  unmounted: cleanup,
}

// Helper: daftarkan global di app (opsional). Bisa juga import per-komponen.
export default vReveal
