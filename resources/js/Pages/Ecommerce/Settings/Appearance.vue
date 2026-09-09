<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { Check, Palette, Trash2 } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import FileUpload from '@/Components/UI/FileUpload.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import FormSectionHeader from '@/Components/UI/FormSectionHeader.vue'
import Input from '@/Components/UI/Input.vue'
import { useAuthorization } from '@/Composables/useAuthorization'
import EcommerceLayout from '@/Layouts/EcommerceLayout.vue'

const props = defineProps({
  storeSettings: { type: Object, required: true },
})

const { can } = useAuthorization()

const templates = [
  { id: 'modern', name: 'Modern', description: 'Hero besar, badge, dan aksen warna tegas.' },
  { id: 'minimal', name: 'Minimal', description: 'Bersih dan sederhana, fokus pada produk.' },
]

const form = useForm({
  template: props.storeSettings.template || 'modern',
  primary_color: props.storeSettings.primary_color || '#1d4ed8',
  secondary_color: props.storeSettings.secondary_color || '',
  tagline: props.storeSettings.tagline || '',
  hero_title: props.storeSettings.hero_title || '',
  hero_subtitle: props.storeSettings.hero_subtitle || '',
  whatsapp_number: props.storeSettings.whatsapp_number || '',
  show_feature_badges: Boolean(props.storeSettings.show_feature_badges),
  is_store_active: Boolean(props.storeSettings.is_store_active),
  banner: null,
  logo: null,
  remove_banner: false,
  remove_logo: false,
})

const bannerPreview = ref(props.storeSettings.banner_url || null)
const logoPreview = ref(props.storeSettings.logo_url || null)

const onBannerChange = (file) => {
  form.banner = file ?? null
  form.remove_banner = !file
  bannerPreview.value = file ? URL.createObjectURL(file) : null
}
const onLogoChange = (file) => {
  form.logo = file ?? null
  form.remove_logo = !file
  logoPreview.value = file ? URL.createObjectURL(file) : null
}
const removeBanner = () => onBannerChange(null)
const removeLogo = () => onLogoChange(null)

const submit = () =>
  form.transform((data) => ({ ...data, _method: 'PUT' })).post(route('ecommerce.settings.appearance.update'), {
    preserveScroll: true,
    forceFormData: true,
  })

const editable = computed(() => can('store.settings.edit'))
</script>

<template>
  <EcommerceLayout active-tab="appearance" title="Tampilan Toko" description="Sesuaikan tema, warna, dan identitas visual toko online Anda.">
    <DataPanel>
      <FormSectionHeader title="Tampilan Toko" description="Template, warna, dan konten hero untuk storefront.">
        <template #icon><Palette :size="18" /></template>
      </FormSectionHeader>

      <form class="space-y-6 p-5 sm:p-6" @submit.prevent="submit">
        <!-- Status toko -->
        <div class="flex items-center justify-between rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Toko Online Aktif</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">Jika dimatikan, storefront tidak dapat diakses pengunjung.</p>
          </div>
          <label class="relative inline-flex cursor-pointer items-center">
            <input type="checkbox" v-model="form.is_store_active" class="peer sr-only" :disabled="!editable" />
            <div class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-emerald-600 peer-checked:after:translate-x-full dark:bg-slate-600"></div>
          </label>
        </div>

        <!-- Template -->
        <div>
          <p class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">Template</p>
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <button
              v-for="t in templates"
              :key="t.id"
              type="button"
              :disabled="!editable"
              @click="form.template = t.id"
              :class="[
                'rounded-xl border p-4 text-left transition',
                form.template === t.id
                  ? 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500/20 dark:bg-emerald-500/10'
                  : 'border-slate-200 hover:border-slate-300 dark:border-[#29476b] dark:hover:border-slate-600',
                !editable ? 'cursor-not-allowed opacity-60' : '',
              ]"
            >
              <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ t.name }}</p>
                <span
                  :class="[
                    'flex h-5 w-5 items-center justify-center rounded-full border-2',
                    form.template === t.id ? 'border-emerald-600 bg-emerald-600' : 'border-slate-300 dark:border-slate-600',
                  ]"
                >
                  <Check v-if="form.template === t.id" :size="12" class="text-white" />
                </span>
              </div>
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ t.description }}</p>
            </button>
          </div>
        </div>

        <!-- Warna -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Warna Utama</label>
            <div class="flex items-center gap-3">
              <input
                type="color"
                v-model="form.primary_color"
                :disabled="!editable"
                class="h-11 w-14 cursor-pointer rounded-lg border border-slate-300 bg-white dark:border-[#29476b]"
              />
              <Input v-model="form.primary_color" placeholder="#1d4ed8" :disabled="!editable" class="flex-1" />
            </div>
            <p v-if="form.errors.primary_color" class="mt-1 text-xs text-red-500">{{ form.errors.primary_color }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Warna Sekunder</label>
            <div class="flex items-center gap-3">
              <input
                type="color"
                v-model="form.secondary_color"
                :disabled="!editable"
                class="h-11 w-14 cursor-pointer rounded-lg border border-slate-300 bg-white dark:border-[#29476b]"
              />
              <Input v-model="form.secondary_color" placeholder="#0f172a" :disabled="!editable" class="flex-1" />
            </div>
          </div>
        </div>

        <!-- Hero -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <Input v-model="form.hero_title" label="Judul Hero" placeholder="Belanja mudah di toko kami" :disabled="!editable" />
          <Input v-model="form.hero_subtitle" label="Sub-judul Hero" placeholder="Produk berkualitas, harga terjangkau" :disabled="!editable" />
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <Input v-model="form.tagline" label="Tagline" placeholder="Toko terpercaya sejak 2020" :disabled="!editable" />
          <Input v-model="form.whatsapp_number" label="Nomor WhatsApp" placeholder="62812xxxxxxx" :disabled="!editable" />
        </div>

        <!-- Badge -->
        <div class="flex items-center justify-between rounded-xl border border-slate-200 p-4 dark:border-[#29476b]">
          <div>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Tampilkan Badge Keunggulan</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">Badge seperti "Pengiriman Cepat", "Produk Asli", dsb.</p>
          </div>
          <label class="relative inline-flex cursor-pointer items-center">
            <input type="checkbox" v-model="form.show_feature_badges" class="peer sr-only" :disabled="!editable" />
            <div class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-emerald-600 peer-checked:after:translate-x-full dark:bg-slate-600"></div>
          </label>
        </div>

        <!-- Banner & Logo -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <p class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">Banner / Hero Image</p>
            <div v-if="bannerPreview" class="relative mb-2 overflow-hidden rounded-xl border border-slate-200 dark:border-[#29476b]">
              <img :src="bannerPreview" alt="Banner" class="h-36 w-full object-cover" />
              <button
                v-if="editable"
                type="button"
                class="absolute right-2 top-2 rounded-lg bg-white/90 p-1.5 text-red-500 shadow hover:bg-white"
                @click="removeBanner"
              >
                <Trash2 :size="14" />
              </button>
            </div>
            <FileUpload v-model="form.banner" label="Upload Banner" :disabled="!editable" @select="onBannerChange" />
          </div>
          <div>
            <p class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">Logo Toko</p>
            <div v-if="logoPreview" class="relative mb-2 inline-block overflow-hidden rounded-xl border border-slate-200 dark:border-[#29476b]">
              <img :src="logoPreview" alt="Logo" class="h-24 w-24 object-contain p-2" />
              <button
                v-if="editable"
                type="button"
                class="absolute right-1 top-1 rounded-lg bg-white/90 p-1 text-red-500 shadow hover:bg-white"
                @click="removeLogo"
              >
                <Trash2 :size="12" />
              </button>
            </div>
            <FileUpload v-model="form.logo" label="Upload Logo" :disabled="!editable" @select="onLogoChange" />
          </div>
        </div>

        <FormActions>
          <Button v-if="editable" type="submit" :loading="form.processing" :disabled="!form.isDirty && !form.banner && !form.logo">
            Simpan Tampilan
          </Button>
        </FormActions>
      </form>
    </DataPanel>
  </EcommerceLayout>
</template>
