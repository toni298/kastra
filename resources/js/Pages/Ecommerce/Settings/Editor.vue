<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
  CreditCard,
  ExternalLink,
  Globe,
  Image as ImageIcon,
  LayoutGrid,
  Monitor,
  PackageCheck,
  Palette,
  RefreshCw,
  Save,
  Smartphone,
  Store,
  Trash2,
  Truck,
  Type,
} from 'lucide-vue-next'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import EditorAccordion from './Components/EditorAccordion.vue'
import SectionManager from './Components/SectionManager.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import { useAuthorization } from '@/Composables/useAuthorization'

const props = defineProps({
  storeSettings: { type: Object, required: true },
  sections: { type: Array, default: () => [] },
  sectionTypes: { type: Object, default: () => ({}) },
  bankAccounts: { type: Array, default: () => [] },
  baseDomain: { type: String, default: '' },
  companyName: { type: String, default: '' },
  companySlug: { type: String, default: '' },
  branchName: { type: String, default: '' },
  branchSlug: { type: String, default: '' },
  storefrontUrl: { type: String, default: null },
})

const { can } = useAuthorization()
const editable = computed(() => can('store.settings.edit'))

// ----- Form utama -----
const form = useForm({
  template: props.storeSettings.template || 'modern',
  primary_color: props.storeSettings.primary_color || '#1d4ed8',
  secondary_color: props.storeSettings.secondary_color || '#0f172a',
  tagline: props.storeSettings.tagline || '',
  hero_title: props.storeSettings.hero_title || '',
  hero_subtitle: props.storeSettings.hero_subtitle || '',
  show_feature_badges: Boolean(props.storeSettings.show_feature_badges),
  whatsapp_number: props.storeSettings.whatsapp_number || '',
  is_store_active: Boolean(props.storeSettings.is_store_active),
  banner: null,
  logo: null,
  remove_banner: false,
  remove_logo: false,
  payment_cod_enabled: Boolean(props.storeSettings.payment_cod_enabled),
  payment_transfer_enabled: Boolean(props.storeSettings.payment_transfer_enabled),
  payment_qris_enabled: Boolean(props.storeSettings.payment_qris_enabled),
  payment_notes: props.storeSettings.payment_notes || '',
  qris_image: null,
  remove_qris_image: false,
  pickup_enabled: Boolean(props.storeSettings.pickup_enabled),
  delivery_enabled: Boolean(props.storeSettings.delivery_enabled),
  flat_shipping_cost: props.storeSettings.flat_shipping_cost ?? 0,
  free_shipping_min: props.storeSettings.free_shipping_min ?? null,
  subdomain: props.storeSettings.subdomain || '',
  custom_domain: props.storeSettings.custom_domain || '',
})

// ----- Live preview data -----
const bannerPreview = ref(props.storeSettings.banner_url || null)
const logoPreview = ref(props.storeSettings.logo_url || null)
const qrisPreview = ref(props.storeSettings.qris_image_url || null)

const onBanner = (f) => {
  form.banner = f ?? null
  form.remove_banner = !f
  bannerPreview.value = f ? URL.createObjectURL(f) : null
}
const onLogo = (f) => {
  form.logo = f ?? null
  form.remove_logo = !f
  logoPreview.value = f ? URL.createObjectURL(f) : null
}
const onQris = (f) => {
  form.qris_image = f ?? null
  form.remove_qris_image = !f
  qrisPreview.value = f ? URL.createObjectURL(f) : null
}

const previewSettings = computed(() => ({
  ...form.data(),
  banner_url: bannerPreview.value,
  logo_url: logoPreview.value,
}))

// ----- Sections manager (lego) -----
const localSections = ref(JSON.parse(JSON.stringify(props.sections)))
const sectionForm = useForm({ sections: [] })
const sectionsDirty = ref(false)

const markSectionsDirty = () => {
  sectionsDirty.value = true
}

const saveSections = () => {
  sectionForm.sections = localSections.value.map((s) => ({
    key: s.key,
    type: s.type,
    label: s.label,
    enabled: s.enabled,
    config: s.config ?? {},
  }))
  sectionForm
    .transform((d) => ({ ...d, _method: 'PUT' }))
    .post(route('ecommerce.settings.sections.update'), {
      preserveScroll: true,
      onSuccess: () => {
        sectionsDirty.value = false
        refreshPreview()
      },
    })
}

// ----- Simpan semua -----
const activePanel = ref('tampilan')
const panels = [
  { id: 'tampilan', label: 'Tampilan', icon: Palette },
  { id: 'konten', label: 'Konten', icon: Type },
  { id: 'section', label: 'Section', icon: LayoutGrid },
  { id: 'pembayaran', label: 'Pembayaran', icon: CreditCard },
  { id: 'pengiriman', label: 'Pengiriman', icon: Truck },
  { id: 'domain', label: 'Domain', icon: Globe },
]

const previewDevice = ref('desktop')

// ----- Live iframe preview -----
// Preview menampilkan halaman store asli via iframe sehingga selalu sinkron
// dengan tampilan nyata. Di-refresh (cache-buster) setiap kali data disimpan
// atau user menekan tombol refresh manual.
const iframeRef = ref(null)
const previewLoading = ref(true)
const previewKey = ref(Date.now())
const previewUrl = computed(() => {
  if (!props.storefrontUrl) return null
  const sep = props.storefrontUrl.includes('?') ? '&' : '?'
  return `${props.storefrontUrl}${sep}_pv=${previewKey.value}`
})
const refreshPreview = () => {
  previewLoading.value = true
  previewKey.value = Date.now()
}
const onIframeLoad = () => {
  previewLoading.value = false
}

const saveAll = () => {
  form
    .transform((d) => ({ ...d, _method: 'PUT' }))
    .post(route('ecommerce.settings.appearance.update'), {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        refreshPreview()
      },
    })
}
</script>

<template>
  <AuthenticatedLayout title="Editor Toko Online">
    <div class="flex h-[calc(100vh-4rem)] flex-col">
      <!-- Topbar editor -->
      <div
        class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 dark:border-[#1d3859] dark:bg-[#0a1b33]"
      >
        <div class="flex items-center gap-3">
          <div
            class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"
          >
            <Store :size="18" />
          </div>
          <div>
            <h1 class="text-sm font-bold text-slate-900 dark:text-white">Editor Toko Online</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ companyName }} — {{ branchName }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <!-- Tombol lihat toko asli -->
          <a
            v-if="storefrontUrl"
            :href="storefrontUrl"
            target="_blank"
            rel="noopener"
            class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 dark:border-[#29476b] dark:text-slate-200 dark:hover:bg-[#0d1e36]"
            title="Buka halaman toko di tab baru"
          >
            <ExternalLink :size="14" />
            Lihat Toko
          </a>

          <!-- Device switcher -->
          <div
            class="hidden items-center rounded-lg border border-slate-200 p-0.5 dark:border-[#29476b] sm:flex"
          >
            <button
              type="button"
              class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition"
              :class="
                previewDevice === 'desktop'
                  ? 'bg-emerald-600 text-white'
                  : 'text-slate-500 dark:text-slate-400'
              "
              @click="previewDevice = 'desktop'"
            >
              <Monitor :size="13" /> Desktop
            </button>
            <button
              type="button"
              class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition"
              :class="
                previewDevice === 'mobile'
                  ? 'bg-emerald-600 text-white'
                  : 'text-slate-500 dark:text-slate-400'
              "
              @click="previewDevice = 'mobile'"
            >
              <Smartphone :size="13" /> Mobile
            </button>
          </div>

          <label
            class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 dark:border-[#29476b]"
          >
            <input
              type="checkbox"
              v-model="form.is_store_active"
              :disabled="!editable"
              class="h-4 w-4 rounded border-slate-300 text-emerald-600"
            />
            <span class="text-xs font-medium text-slate-700 dark:text-slate-200">Toko Aktif</span>
          </label>

          <Button v-if="editable" type="button" :loading="form.processing" @click="saveAll">
            <Save :size="15" class="mr-1.5" /> Simpan
          </Button>
        </div>
      </div>

      <!-- Body -->
      <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar kiri -->
        <aside
          class="w-full max-w-sm shrink-0 overflow-y-auto border-r border-slate-200 bg-slate-50 p-4 dark:border-[#1d3859] dark:bg-[#081627]"
        >
          <!-- Nav panel -->
          <div
            class="mb-4 grid grid-cols-3 gap-1.5 rounded-xl border border-slate-200 bg-white p-1.5 dark:border-[#29476b] dark:bg-[#0d1e36]"
          >
            <button
              v-for="p in panels"
              :key="p.id"
              type="button"
              class="flex flex-col items-center gap-1 rounded-lg px-1 py-2 text-[11px] font-medium transition"
              :class="
                activePanel === p.id
                  ? 'bg-emerald-600 text-white'
                  : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-[#0a1b33]'
              "
              @click="activePanel = p.id"
            >
              <component :is="p.icon" :size="15" />
              {{ p.label }}
            </button>
          </div>

          <div class="space-y-3">
            <!-- TAMPILAN -->
            <template v-if="activePanel === 'tampilan'">
              <EditorAccordion title="Warna & Tema" :icon="Palette" :default-open="true">
                <div class="space-y-3">
                  <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300"
                      >Warna Utama</label
                    >
                    <div class="flex items-center gap-2">
                      <input
                        type="color"
                        v-model="form.primary_color"
                        :disabled="!editable"
                        class="h-9 w-12 cursor-pointer rounded border border-slate-200 dark:border-[#29476b]"
                      />
                      <Input v-model="form.primary_color" :disabled="!editable" class="flex-1" />
                    </div>
                  </div>
                  <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300"
                      >Warna Sekunder</label
                    >
                    <div class="flex items-center gap-2">
                      <input
                        type="color"
                        v-model="form.secondary_color"
                        :disabled="!editable"
                        class="h-9 w-12 cursor-pointer rounded border border-slate-200 dark:border-[#29476b]"
                      />
                      <Input v-model="form.secondary_color" :disabled="!editable" class="flex-1" />
                    </div>
                  </div>
                  <label
                    class="flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 p-3 dark:border-[#29476b]"
                  >
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-200"
                      >Tampilkan Badge Keunggulan</span
                    >
                    <input
                      type="checkbox"
                      v-model="form.show_feature_badges"
                      :disabled="!editable"
                      class="h-4 w-4 rounded border-slate-300 text-emerald-600"
                    />
                  </label>
                </div>
              </EditorAccordion>

              <EditorAccordion title="Banner & Logo" :icon="ImageIcon">
                <div class="space-y-4">
                  <div>
                    <p class="mb-1.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                      Banner Hero
                    </p>
                    <div
                      v-if="bannerPreview"
                      class="relative mb-2 overflow-hidden rounded-lg border border-slate-200 dark:border-[#29476b]"
                    >
                      <img :src="bannerPreview" class="h-24 w-full object-cover" alt="" />
                      <button
                        v-if="editable"
                        type="button"
                        class="absolute right-1 top-1 rounded bg-white/90 p-1 text-red-500"
                        @click="onBanner(null)"
                      >
                        <Trash2 :size="11" />
                      </button>
                    </div>
                    <FileUpload
                      v-model="form.banner"
                      label="Upload Banner"
                      :disabled="!editable"
                      @select="onBanner"
                    />
                  </div>
                  <div>
                    <p class="mb-1.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                      Logo Toko
                    </p>
                    <div
                      v-if="logoPreview"
                      class="relative mb-2 inline-block overflow-hidden rounded-lg border border-slate-200 dark:border-[#29476b]"
                    >
                      <img :src="logoPreview" class="h-16 w-16 object-contain p-1" alt="" />
                      <button
                        v-if="editable"
                        type="button"
                        class="absolute right-1 top-1 rounded bg-white/90 p-1 text-red-500"
                        @click="onLogo(null)"
                      >
                        <Trash2 :size="11" />
                      </button>
                    </div>
                    <FileUpload
                      v-model="form.logo"
                      label="Upload Logo"
                      :disabled="!editable"
                      @select="onLogo"
                    />
                  </div>
                </div>
              </EditorAccordion>
            </template>

            <!-- KONTEN -->
            <template v-else-if="activePanel === 'konten'">
              <EditorAccordion title="Teks Hero" :icon="Type" :default-open="true">
                <div class="space-y-3">
                  <Input
                    v-model="form.tagline"
                    label="Tagline"
                    placeholder="Tagline singkat toko"
                    :disabled="!editable"
                  />
                  <Input
                    v-model="form.hero_title"
                    label="Judul Hero"
                    placeholder="Judul besar di banner"
                    :disabled="!editable"
                  />
                  <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300"
                      >Sub-judul Hero</label
                    >
                    <textarea
                      v-model="form.hero_subtitle"
                      rows="2"
                      :disabled="!editable"
                      placeholder="Deskripsi singkat di bawah judul"
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
                    ></textarea>
                  </div>
                </div>
              </EditorAccordion>

              <EditorAccordion title="Kontak" :icon="Phone">
                <Input
                  v-model="form.whatsapp_number"
                  label="Nomor WhatsApp"
                  placeholder="62812xxxxxxx"
                  :disabled="!editable"
                />
              </EditorAccordion>
            </template>

            <!-- SECTION -->
            <template v-else-if="activePanel === 'section'">
              <EditorAccordion title="Kelola Section" :icon="LayoutGrid" :default-open="true">
                <SectionManager
                  v-model:sections="localSections"
                  :section-types="sectionTypes"
                  :editable="editable"
                  :dirty="sectionsDirty"
                  :processing="sectionForm.processing"
                  @dirty="markSectionsDirty"
                  @save="saveSections"
                />
              </EditorAccordion>
            </template>

            <!-- PEMBAYARAN -->
            <template v-else-if="activePanel === 'pembayaran'">
              <EditorAccordion title="Metode Pembayaran" :icon="CreditCard" :default-open="true">
                <div class="space-y-2">
                  <label
                    v-for="o in [
                      { k: 'payment_cod_enabled', l: 'COD (Bayar di Tempat)' },
                      { k: 'payment_transfer_enabled', l: 'Transfer Bank' },
                      { k: 'payment_qris_enabled', l: 'QRIS' },
                    ]"
                    :key="o.k"
                    class="flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 p-3 dark:border-[#29476b]"
                  >
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-200">{{
                      o.l
                    }}</span>
                    <input
                      type="checkbox"
                      v-model="form[o.k]"
                      :disabled="!editable"
                      class="h-4 w-4 rounded border-slate-300 text-emerald-600"
                    />
                  </label>
                </div>
              </EditorAccordion>

              <EditorAccordion
                v-if="form.payment_qris_enabled"
                title="Gambar QRIS"
                :icon="ImageIcon"
              >
                <div
                  v-if="qrisPreview"
                  class="relative mb-2 inline-block overflow-hidden rounded-lg border border-slate-200 dark:border-[#29476b]"
                >
                  <img :src="qrisPreview" class="h-32 w-32 object-contain p-1" alt="" />
                  <button
                    v-if="editable"
                    type="button"
                    class="absolute right-1 top-1 rounded bg-white/90 p-1 text-red-500"
                    @click="onQris(null)"
                  >
                    <Trash2 :size="11" />
                  </button>
                </div>
                <FileUpload
                  v-model="form.qris_image"
                  label="Upload QRIS"
                  :disabled="!editable"
                  @select="onQris"
                />
              </EditorAccordion>

              <EditorAccordion title="Catatan Pembayaran" :icon="Type">
                <textarea
                  v-model="form.payment_notes"
                  rows="3"
                  :disabled="!editable"
                  placeholder="Instruksi pembayaran untuk pelanggan..."
                  class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-white"
                ></textarea>
              </EditorAccordion>
            </template>

            <!-- PENGIRIMAN -->
            <template v-else-if="activePanel === 'pengiriman'">
              <EditorAccordion title="Metode Pengiriman" :icon="Truck" :default-open="true">
                <div class="space-y-2">
                  <label
                    class="flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 p-3 dark:border-[#29476b]"
                  >
                    <span
                      class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-200"
                      ><PackageCheck :size="14" /> Ambil Sendiri (Pickup)</span
                    >
                    <input
                      type="checkbox"
                      v-model="form.pickup_enabled"
                      :disabled="!editable"
                      class="h-4 w-4 rounded border-slate-300 text-emerald-600"
                    />
                  </label>
                  <label
                    class="flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 p-3 dark:border-[#29476b]"
                  >
                    <span
                      class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-200"
                      ><Truck :size="14" /> Diantar (Delivery)</span
                    >
                    <input
                      type="checkbox"
                      v-model="form.delivery_enabled"
                      :disabled="!editable"
                      class="h-4 w-4 rounded border-slate-300 text-emerald-600"
                    />
                  </label>
                </div>
              </EditorAccordion>

              <EditorAccordion v-if="form.delivery_enabled" title="Tarif Ongkir" :icon="Truck">
                <div class="space-y-3">
                  <CurrencyInput
                    v-model="form.flat_shipping_cost"
                    label="Ongkir Flat (Rp)"
                    :disabled="!editable"
                  />
                  <CurrencyInput
                    v-model="form.free_shipping_min"
                    label="Gratis Ongkir Min. (Rp)"
                    hint="Kosongkan bila tidak ada"
                    :disabled="!editable"
                  />
                </div>
              </EditorAccordion>
            </template>

            <!-- DOMAIN -->
            <template v-else-if="activePanel === 'domain'">
              <EditorAccordion title="Subdomain" :icon="Globe" :default-open="true">
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300"
                  >Subdomain</label
                >
                <div class="flex items-stretch">
                  <Input
                    v-model="form.subdomain"
                    placeholder="tokosaya"
                    :disabled="!editable"
                    class="flex-1 rounded-r-none"
                  />
                  <span
                    class="inline-flex items-center rounded-r-lg border border-l-0 border-slate-300 bg-slate-100 px-2 text-[11px] text-slate-500 dark:border-[#29476b] dark:bg-[#0a1b33] dark:text-slate-400"
                    >.{{ baseDomain }}</span
                  >
                </div>
              </EditorAccordion>

              <EditorAccordion title="Custom Domain" :icon="Globe">
                <Input
                  v-model="form.custom_domain"
                  label="Custom Domain"
                  placeholder="tokosaya.com"
                  :disabled="!editable"
                />
                <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                  Arahkan CNAME ke {{ baseDomain }}. Aktivasi bertahap.
                </p>
              </EditorAccordion>
            </template>
          </div>
        </aside>

        <!-- Preview kanan (iframe live ke halaman store) -->
        <main
          class="flex flex-1 items-stretch justify-center overflow-hidden bg-slate-100 p-6 dark:bg-[#050f1e]"
        >
          <div
            class="flex flex-col transition-all duration-300"
            :class="previewDevice === 'mobile' ? 'w-[375px]' : 'w-full max-w-6xl'"
          >
            <div class="mb-3 flex items-center justify-between">
              <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                Live Preview
              </p>
              <div class="flex items-center gap-2">
                <span
                  class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"
                  >Real-time</span
                >
                <button
                  v-if="previewUrl"
                  type="button"
                  class="flex items-center gap-1 rounded-md border border-slate-200 px-2 py-0.5 text-[10px] font-medium text-slate-600 transition hover:bg-white dark:border-[#29476b] dark:text-slate-300 dark:hover:bg-[#0d1e36]"
                  title="Muat ulang preview"
                  @click="refreshPreview"
                >
                  <RefreshCw :size="11" :class="{ 'animate-spin': previewLoading }" />
                  Refresh
                </button>
              </div>
            </div>

            <div
              class="relative flex-1 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-[#1d3859] dark:bg-[#0a1b33]"
            >
              <template v-if="previewUrl">
                <!-- Loading overlay -->
                <div
                  v-if="previewLoading"
                  class="absolute inset-0 z-10 flex items-center justify-center bg-white/80 backdrop-blur-sm dark:bg-[#0a1b33]/80"
                >
                  <RefreshCw
                    :size="22"
                    class="animate-spin text-emerald-600 dark:text-emerald-400"
                  />
                </div>
                <iframe
                  ref="iframeRef"
                  :key="previewUrl"
                  :src="previewUrl"
                  class="h-full w-full border-0"
                  :class="previewDevice === 'mobile' ? '' : 'min-h-[70vh]'"
                  title="Live Preview Toko"
                  @load="onIframeLoad"
                ></iframe>
              </template>
              <div
                v-else
                class="flex h-full min-h-[70vh] flex-col items-center justify-center gap-2 p-8 text-center"
              >
                <Store :size="28" class="text-slate-300 dark:text-slate-600" />
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                  Preview tidak tersedia
                </p>
                <p class="text-xs text-slate-400 dark:text-slate-500">
                  Aktifkan fitur ecommerce & toko pada cabang untuk melihat live preview.
                </p>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
