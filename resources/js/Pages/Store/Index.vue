<script setup>
import { computed } from 'vue'
import { resolveTheme } from './Themes/index.js'

const props = defineProps({
  store: { type: Object, required: true },
  latest: { type: Array, default: () => [] },
  bestSellers: { type: Array, default: () => [] },
  promos: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
})

const theme = computed(() => props.store.theme ?? {})

// Pilih komponen berdasarkan tema toko.
const T = resolveTheme(props.store.theme?.template)
const { StoreLayout } = T

// Pemetaan type section -> komponen (dari tema aktif).
const sectionComponents = {
  hero: T.StoreHeroSection,
  badges: T.StoreBadgesSection,
  categories: T.StoreCategoriesSection,
  latest: T.StoreLatestSection,
  promos: T.StorePromosSection,
  promo_banner: T.PromoBanner,
  advantages: T.StoreAdvantagesSection,
  testimonials: T.StoreTestimonialsSection,
  richtext: T.StoreRichTextSection,
}

// Sections aktif, terurut. store.sections sudah dinormalisasi backend
// (key/type/config). Bila kosong, tampilkan default urut.
const orderedSections = computed(() => {
  const list = (props.store.sections ?? []).filter((s) => s && s.type)
  return [...list].sort((a, b) => (a.sort ?? 0) - (b.sort ?? 0))
})

// Props per type section.
function propsFor(section) {
  const base = { store: props.store, config: section.config ?? {} }
  switch (section.type) {
    case 'categories':
      return { ...base, categories: props.categories }
    case 'latest':
      return { ...base, products: props.latest }
    case 'promos':
      return { ...base, products: props.promos }
    default:
      return base
  }
}

// Section boleh disembunyikan (mis. badges dimatikan dari pengaturan tampilan).
function isVisible(section) {
  if (section.type === 'badges' && (theme.value.show_feature_badges ?? true) === false) return false
  return true
}
</script>

<template>
  <StoreLayout :store="store">
    <template v-for="section in orderedSections" :key="section.key">
      <component
        :is="sectionComponents[section.type]"
        v-if="sectionComponents[section.type] && isVisible(section)"
        v-bind="propsFor(section)"
      />
    </template>
  </StoreLayout>
</template>
