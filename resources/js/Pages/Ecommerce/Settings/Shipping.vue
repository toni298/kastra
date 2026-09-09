<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { PackageCheck, Truck } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import CurrencyInput from '@/Components/UI/CurrencyInput.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import FormActions from '@/Components/UI/FormActions.vue'
import FormSectionHeader from '@/Components/UI/FormSectionHeader.vue'
import { useAuthorization } from '@/Composables/useAuthorization'
import EcommerceLayout from '@/Layouts/EcommerceLayout.vue'

const props = defineProps({
  storeSettings: { type: Object, required: true },
})

const { can } = useAuthorization()
const editable = computed(() => can('store.settings.edit'))

const form = useForm({
  pickup_enabled: Boolean(props.storeSettings.pickup_enabled),
  delivery_enabled: Boolean(props.storeSettings.delivery_enabled),
  flat_shipping_cost: props.storeSettings.flat_shipping_cost ?? 0,
  free_shipping_min: props.storeSettings.free_shipping_min ?? null,
})

const submit = () => form.put(route('ecommerce.settings.shipping.update'), { preserveScroll: true })
</script>

<template>
  <EcommerceLayout active-tab="shipping" title="Pengiriman" description="Atur metode pengambilan dan biaya kirim pesanan.">
    <DataPanel>
      <FormSectionHeader title="Pengiriman" description="Metode pickup/delivery dan tarif ongkir.">
        <template #icon><Truck :size="18" /></template>
      </FormSectionHeader>

      <form class="space-y-6 p-5 sm:p-6" @submit.prevent="submit">
        <!-- Metode -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <label class="flex cursor-pointer items-start justify-between gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 dark:border-[#29476b]">
            <div class="flex items-start gap-3">
              <PackageCheck :size="20" class="mt-0.5 text-emerald-600" />
              <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">Ambil Sendiri (Pickup)</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Pelanggan mengambil pesanan di toko</p>
              </div>
            </div>
            <input type="checkbox" v-model="form.pickup_enabled" :disabled="!editable" class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
          </label>

          <label class="flex cursor-pointer items-start justify-between gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 dark:border-[#29476b]">
            <div class="flex items-start gap-3">
              <Truck :size="20" class="mt-0.5 text-emerald-600" />
              <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">Diantar (Delivery)</p>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Pesanan diantar ke alamat pelanggan</p>
              </div>
            </div>
            <input type="checkbox" v-model="form.delivery_enabled" :disabled="!editable" class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
          </label>
        </div>

        <!-- Ongkir -->
        <div v-if="form.delivery_enabled" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <CurrencyInput
            v-model="form.flat_shipping_cost"
            label="Ongkir Flat (Rp)"
            hint="Biaya kirim tetap untuk semua pesanan"
            :disabled="!editable"
          />
          <CurrencyInput
            v-model="form.free_shipping_min"
            label="Gratis Ongkir Minimal Belanja (Rp)"
            hint="Kosongkan jika tidak ada gratis ongkir"
            :disabled="!editable"
          />
        </div>

        <FormActions>
          <Button v-if="editable" type="submit" :loading="form.processing">Simpan Pengiriman</Button>
        </FormActions>
      </form>
    </DataPanel>
  </EcommerceLayout>
</template>
