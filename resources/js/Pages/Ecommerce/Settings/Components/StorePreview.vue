<script setup>
import { computed } from 'vue'
import {
  BadgePercent,
  CreditCard,
  Headset,
  ImageOff,
  MapPin,
  MessageSquareText,
  PencilRuler,
  Phone,
  RotateCcw,
  Ruler,
  Search,
  ShoppingCart,
  Store as StoreIcon,
  Truck,
  Zap,
} from 'lucide-vue-next'

const props = defineProps({
  settings: { type: Object, required: true },
  sections: { type: Array, default: () => [] },
  companyName: { type: String, default: 'Nama Toko' },
  branchName: { type: String, default: 'Cabang' },
})

const primary = computed(() => props.settings.primary_color || '#1d4ed8')
const secondary = computed(() => props.settings.secondary_color || '#0f172a')
const tagline = computed(() => props.settings.tagline || 'Tagline toko Anda')
const heroTitle = computed(() => props.settings.hero_title || 'Judul Hero Toko Anda')
const heroSubtitle = computed(() => props.settings.hero_subtitle || 'Sub-judul hero akan tampil di sini.')
const banner = computed(() => props.settings.banner_url || null)
const logo = computed(() => props.settings.logo_url || null)

const has = (id) => {
  const s = props.sections.find((x) => x.id === id)
  return s ? s.enabled : true
}
const ordered = computed(() => [...props.sections].sort((a, b) => a.sort - b.sort))

const badges = [
  { icon: PencilRuler, title: 'Custom Desain' },
  { icon: BadgePercent, title: 'Bahan Berkualitas' },
  { icon: Ruler, title: 'Request Ukuran' },
  { icon: Zap, title: 'Pengerjaan Cepat' },
  { icon: MessageSquareText, title: 'Konsultasi Desain' },
]
const services = [
  { icon: Truck, title: 'Gratis Ongkir', desc: 'Ambil di toko' },
  { icon: CreditCard, title: 'Bayar Aman', desc: 'Konfirmasi admin' },
  { icon: RotateCcw, title: 'Garansi', desc: 'Retur 30 hari' },
  { icon: Headset, title: 'Support', desc: '24/7' },
]
const sampleProducts = [
  { name: 'Produk Contoh 1', price: 125000 },
  { name: 'Produk Contoh 2', price: 89000 },
  { name: 'Produk Contoh 3', price: 210000 },
  { name: 'Produk Contoh 4', price: 75000 },
]
const fmt = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n)
</script>

<template>
  <div class="overflow-hidden rounded-xl border border-slate-200 bg-white text-left shadow-sm dark:border-[#29476b] dark:bg-[#0d1e36]">
    <!-- Topbar -->
    <div class="border-b border-slate-100 bg-slate-50 px-3 py-1 dark:border-[#1d3859] dark:bg-[#0a1b33]">
      <p class="truncate text-[10px] text-slate-500 dark:text-slate-400">
        Selamat datang di {{ companyName }} — {{ branchName }}<template v-if="tagline"> · {{ tagline }}</template>
      </p>
    </div>

    <!-- Header -->
    <div class="flex items-center gap-3 border-b border-slate-100 px-3 py-2 dark:border-[#1d3859]">
      <div class="flex items-center gap-1.5">
        <img v-if="logo" :src="logo" class="h-6 w-6 rounded object-contain" alt="" />
        <span v-else class="flex h-6 w-6 items-center justify-center rounded text-white" :style="{ backgroundColor: primary }">
          <StoreIcon :size="12" />
        </span>
        <div class="leading-tight">
          <p class="text-[11px] font-extrabold text-slate-900 dark:text-white">{{ companyName }}</p>
          <p class="text-[8px] font-semibold uppercase tracking-widest" :style="{ color: primary }">{{ branchName }}</p>
        </div>
      </div>
      <div class="mx-auto flex flex-1 items-center overflow-hidden rounded-sm border border-slate-200 dark:border-[#29476b]">
        <input disabled placeholder="Cari produk..." class="h-6 w-full bg-transparent px-2 text-[9px] outline-none dark:text-slate-300" />
        <span class="flex h-6 items-center px-2 text-white" :style="{ backgroundColor: primary }"><Search :size="10" /></span>
      </div>
      <ShoppingCart :size="14" class="text-slate-500" />
    </div>

    <!-- Nav -->
    <div class="flex gap-2 border-b border-slate-100 px-3 py-1 dark:border-[#1d3859]">
      <span class="border-b-2 px-1.5 py-0.5 text-[9px] font-semibold" :style="{ borderColor: primary, color: primary }">Beranda</span>
      <span class="px-1.5 py-0.5 text-[9px] text-slate-500 dark:text-slate-400">Belanja</span>
      <span class="px-1.5 py-0.5 text-[9px] text-slate-500 dark:text-slate-400">Keranjang</span>
    </div>

    <!-- Body sections -->
    <div class="space-y-3 p-3">
      <!-- Hero -->
      <section v-if="has('hero')" class="relative overflow-hidden rounded-sm" :style="{ backgroundColor: secondary }">
        <img v-if="banner" :src="banner" class="absolute inset-0 h-full w-full object-cover opacity-40" alt="" />
        <div class="relative flex aspect-[21/9] flex-col justify-center px-4">
          <p class="text-[8px] font-semibold uppercase tracking-widest text-white/70">{{ tagline }}</p>
          <h3 class="mt-0.5 max-w-[70%] text-sm font-extrabold leading-tight text-white">{{ heroTitle }}</h3>
          <p class="mt-0.5 hidden max-w-[60%] text-[8px] text-white/70 sm:block">{{ heroSubtitle }}</p>
          <span class="mt-1.5 w-fit rounded-sm px-2 py-1 text-[8px] font-bold uppercase text-white" :style="{ backgroundColor: primary }">Belanja Sekarang</span>
        </div>
      </section>

      <!-- Badges -->
      <section v-if="has('badges') && (settings.show_feature_badges ?? true)" class="grid grid-cols-5 gap-1.5">
        <div v-for="b in badges" :key="b.title" class="flex flex-col items-center rounded border border-slate-100 p-1.5 text-center dark:border-[#1d3859]">
          <component :is="b.icon" :size="12" :style="{ color: primary }" />
          <p class="mt-1 text-[7px] font-medium leading-tight text-slate-700 dark:text-slate-300">{{ b.title }}</p>
        </div>
      </section>

      <!-- Sections dinamis urut -->
      <template v-for="sec in ordered" :key="sec.id">
        <section v-if="['categories', 'latest', 'promos'].includes(sec.id) && sec.enabled">
          <div class="mb-1 flex items-center justify-between">
            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-900 dark:text-white">{{ sec.label }}</p>
            <span class="text-[8px]" :style="{ color: primary }">Lihat Semua</span>
          </div>
          <div v-if="sec.id === 'categories'" class="grid grid-cols-4 gap-1.5">
            <div v-for="i in 4" :key="i" class="aspect-square rounded border border-slate-100 bg-slate-50 dark:border-[#1d3859] dark:bg-[#0a1b33]"></div>
          </div>
          <div v-else class="grid grid-cols-4 gap-1.5">
            <div v-for="p in sampleProducts" :key="p.name" class="rounded border border-slate-100 p-1 dark:border-[#1d3859]">
              <div class="flex aspect-square items-center justify-center rounded bg-slate-100 dark:bg-[#0a1b33]"><ImageOff :size="12" class="text-slate-300" /></div>
              <p class="mt-1 truncate text-[8px] font-medium text-slate-800 dark:text-slate-200">{{ p.name }}</p>
              <p class="text-[8px] font-bold" :style="{ color: primary }">{{ fmt(p.price) }}</p>
            </div>
          </div>
        </section>
      </template>

      <!-- Services -->
      <section v-if="has('badges')" class="grid grid-cols-4 gap-1.5 border-t border-slate-100 pt-2 dark:border-[#1d3859]">
        <div v-for="s in services" :key="s.title" class="flex items-start gap-1">
          <component :is="s.icon" :size="11" class="mt-0.5 shrink-0" :style="{ color: primary }" />
          <div>
            <p class="text-[7px] font-semibold text-slate-800 dark:text-slate-200">{{ s.title }}</p>
            <p class="text-[6px] text-slate-500 dark:text-slate-400">{{ s.desc }}</p>
          </div>
        </div>
      </section>
    </div>

    <!-- Footer -->
    <div class="px-3 py-2" :style="{ backgroundColor: secondary }">
      <p class="text-[8px] font-bold text-white">{{ companyName }} — {{ branchName }}</p>
      <p class="text-[7px] text-white/50">© {{ new Date().getFullYear() }} {{ companyName }}</p>
    </div>
  </div>
</template>
