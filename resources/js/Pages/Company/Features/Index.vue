<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
  ChartNoAxesColumn,
  Check,
  Globe,
  Handshake,
  Landmark,
  Package,
  Puzzle,
  Receipt,
  ShoppingBag,
  ShoppingCart,
  Users,
  Warehouse,
} from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import FormSectionHeader from '@/Components/UI/FormSectionHeader.vue'
import { useAuthorization } from '@/Composables/useAuthorization'
import CompanyLayout from '@/Layouts/CompanyLayout.vue'

const props = defineProps({
  companyFeatures: { type: Array, default: () => [] },
  featureCatalog: { type: Array, required: true },
})

const { can } = useAuthorization()

const icons = {
  ShoppingCart,
  Package,
  ShoppingBag,
  Receipt,
  Warehouse,
  Landmark,
  Users,
  ChartNoAxesColumn,
  Globe,
  Handshake,
}

const form = useForm({
  features: [...props.companyFeatures],
})

const toggle = (feature) => {
  if (!feature.enabled) return
  const idx = form.features.indexOf(feature.id)
  if (idx === -1) {
    form.features.push(feature.id)
  } else {
    form.features.splice(idx, 1)
  }
}

const isSelected = (id) => form.features.includes(id)

const isDirty = computed(() => {
  const a = [...form.features].sort()
  const b = [...props.companyFeatures].sort()
  return JSON.stringify(a) !== JSON.stringify(b)
})

const submit = () =>
  form.put(route('company.features.update'), { preserveScroll: true })
</script>

<template>
  <CompanyLayout active-tab="features">
    <DataPanel>
      <FormSectionHeader
        title="Fitur Perusahaan"
        description="Aktifkan atau nonaktifkan modul sesuai kebutuhan bisnis Anda. Perubahan akan langsung menyesuaikan menu dan hak akses semua pengguna."
      >
        <template #icon><Puzzle :size="18" /></template>
      </FormSectionHeader>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 sm:p-6">
          <button
            v-for="feature in featureCatalog"
            :key="feature.id"
            type="button"
            :disabled="!feature.enabled"
            @click="toggle(feature)"
            :class="[
              'group relative rounded-xl border p-4 text-left transition-all duration-200',
              !feature.enabled
                ? 'cursor-not-allowed border-slate-200 bg-slate-50 opacity-60 dark:border-[#29476b] dark:bg-slate-800/50'
                : isSelected(feature.id)
                  ? 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500/20 dark:border-emerald-500 dark:bg-emerald-500/10'
                  : 'border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm dark:border-[#29476b] dark:bg-slate-800 dark:hover:border-slate-600',
            ]"
          >
            <div class="flex items-start gap-3">
              <div
                :class="[
                  'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-colors',
                  isSelected(feature.id) && feature.enabled
                    ? 'bg-emerald-600 text-white'
                    : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300',
                ]"
              >
                <component :is="icons[feature.icon] || Puzzle" :size="20" />
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <p class="text-sm font-semibold text-slate-900 dark:text-white">
                    {{ feature.name }}
                  </p>
                  <span
                    v-if="!feature.enabled"
                    class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-400"
                  >
                    Segera Hadir
                  </span>
                </div>
                <p class="mt-0.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                  {{ feature.description }}
                </p>
                <p
                  v-if="feature.id === 'pos'"
                  class="mt-2 flex items-center gap-1.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400"
                >
                  <Users :size="12" />
                  Role "Kasir" dengan permission standar POS dibuat otomatis saat fitur ini aktif,
                  dan dinonaktifkan saat fitur tidak dipakai.
                </p>
              </div>
              <div
                v-if="feature.enabled"
                :class="[
                  'flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition-all duration-200',
                  isSelected(feature.id)
                    ? 'border-emerald-600 bg-emerald-600'
                    : 'border-slate-300 group-hover:border-slate-400 dark:border-slate-600',
                ]"
              >
                <Check v-if="isSelected(feature.id)" :size="12" class="text-white" />
              </div>
            </div>
          </button>
        </div>

        <p class="px-5 pb-2 text-xs text-slate-400 sm:px-6 dark:text-slate-500">
          {{ form.features.length }} fitur aktif. Menu di sidebar dan hak akses role akan otomatis menyesuaikan setelah disimpan.
        </p>
        <p v-if="form.errors.features" class="px-5 pb-2 text-xs text-red-500 sm:px-6">
          {{ form.errors.features }}
        </p>

        <FormActions>
          <Button
            v-if="can('company.settings')"
            type="submit"
            :loading="form.processing"
            :disabled="!isDirty"
          >
            Simpan Perubahan
          </Button>
        </FormActions>
      </form>
    </DataPanel>
  </CompanyLayout>
</template>
