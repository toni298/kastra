<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Globe, Info } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import FormSectionHeader from '@/Components/UI/FormSectionHeader.vue'
import Input from '@/Components/UI/Input.vue'
import Badge from '@/Components/UI/Badge.vue'
import { useAuthorization } from '@/Composables/useAuthorization'
import EcommerceLayout from '@/Layouts/EcommerceLayout.vue'

const props = defineProps({
  storeSettings: { type: Object, required: true },
  baseDomain: { type: String, default: '' },
})

const { can } = useAuthorization()
const editable = computed(() => can('store.settings.edit'))

const form = useForm({
  subdomain: props.storeSettings.subdomain || '',
  custom_domain: props.storeSettings.custom_domain || '',
})

const submit = () => form.put(route('ecommerce.settings.domain.update'), { preserveScroll: true })
</script>

<template>
  <EcommerceLayout active-tab="domain" title="Domain" description="Atur alamat toko online Anda.">
    <DataPanel>
      <FormSectionHeader title="Domain" description="Subdomain dan custom domain untuk toko online.">
        <template #icon><Globe :size="18" /></template>
      </FormSectionHeader>

      <div class="m-5 flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-500/30 dark:bg-blue-500/10 sm:m-6">
        <Info :size="18" class="mt-0.5 shrink-0 text-blue-600 dark:text-blue-400" />
        <p class="text-sm text-blue-800 dark:text-blue-300">
          Fitur domain saat ini <strong>disimpan sebagai konfigurasi</strong> dan akan <strong>diaktifkan pada tahap berikutnya</strong>.
          Aktivasi memerlukan pengaturan DNS / wildcard subdomain di server.
        </p>
      </div>

      <form class="space-y-6 p-5 sm:p-6" @submit.prevent="submit">
        <!-- Subdomain -->
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Subdomain</label>
          <div class="flex items-stretch">
            <Input
              v-model="form.subdomain"
              placeholder="tokosaya"
              :disabled="!editable"
              class="flex-1 rounded-r-none"
            />
            <span class="inline-flex items-center rounded-r-xl border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500 dark:border-[#29476b] dark:bg-[#0d1e36] dark:text-slate-400">
              .{{ baseDomain }}
            </span>
          </div>
          <p v-if="form.errors.subdomain" class="mt-1 text-xs text-red-500">{{ form.errors.subdomain }}</p>
          <p v-if="form.subdomain" class="mt-1.5 text-xs text-slate-500">
            Alamat toko: <span class="font-medium text-emerald-600">https://{{ form.subdomain }}.{{ baseDomain }}</span>
          </p>
        </div>

        <!-- Custom domain -->
        <div>
          <div class="mb-1.5 flex items-center gap-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Custom Domain</label>
            <Badge variant="warning">Segera Hadir</Badge>
          </div>
          <Input v-model="form.custom_domain" placeholder="tokosaya.com" :disabled="!editable" />
          <p v-if="form.errors.custom_domain" class="mt-1 text-xs text-red-500">{{ form.errors.custom_domain }}</p>
          <p class="mt-1.5 text-xs text-slate-500">
            Arahkan DNS (CNAME) domain Anda ke <code class="rounded bg-slate-100 px-1 dark:bg-[#0d1e36]">{{ baseDomain }}</code>
          </p>
        </div>

        <FormActions>
          <Button v-if="editable" type="submit" :loading="form.processing" :disabled="!form.isDirty">Simpan Domain</Button>
        </FormActions>
      </form>
    </DataPanel>
  </EcommerceLayout>
</template>
