<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { CreditCard, Plus, Trash2, Landmark } from 'lucide-vue-next'
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
  bankAccounts: { type: Array, default: () => [] },
})

const { can } = useAuthorization()
const editable = computed(() => can('store.settings.edit'))

const form = useForm({
  payment_cod_enabled: Boolean(props.storeSettings.payment_cod_enabled),
  payment_transfer_enabled: Boolean(props.storeSettings.payment_transfer_enabled),
  payment_qris_enabled: Boolean(props.storeSettings.payment_qris_enabled),
  payment_notes: props.storeSettings.payment_notes || '',
  qris_image: null,
  remove_qris_image: false,
  bank_accounts: props.bankAccounts.map((b) => ({
    bank_name: b.bank_name,
    account_number: b.account_number,
    account_name: b.account_name,
    is_active: Boolean(b.is_active),
  })),
})

const qrisPreview = ref(props.storeSettings.qris_image_url || null)
const onQrisChange = (file) => {
  form.qris_image = file ?? null
  form.remove_qris_image = !file
  qrisPreview.value = file ? URL.createObjectURL(file) : null
}
const removeQris = () => onQrisChange(null)

const addAccount = () =>
  form.bank_accounts.push({ bank_name: '', account_number: '', account_name: '', is_active: true })
const removeAccount = (i) => form.bank_accounts.splice(i, 1)

const submit = () =>
  form.transform((data) => ({ ...data, _method: 'PUT' })).post(route('ecommerce.settings.payment.update'), {
    preserveScroll: true,
    forceFormData: true,
  })
</script>

<template>
  <EcommerceLayout active-tab="payment" title="Metode Pembayaran" description="Atur cara pelanggan membayar pesanan toko online Anda.">
    <DataPanel>
      <FormSectionHeader title="Pembayaran" description="Aktifkan metode pembayaran yang tersedia di checkout.">
        <template #icon><CreditCard :size="18" /></template>
      </FormSectionHeader>

      <form class="space-y-6 p-5 sm:p-6" @submit.prevent="submit">
        <!-- Toggles -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <label
            v-for="opt in [
              { key: 'payment_cod_enabled', label: 'COD', desc: 'Bayar di tempat saat barang diterima' },
              { key: 'payment_transfer_enabled', label: 'Transfer Bank', desc: 'Transfer manual ke rekening' },
              { key: 'payment_qris_enabled', label: 'QRIS', desc: 'Scan kode QRIS untuk membayar' },
            ]"
            :key="opt.key"
            class="flex cursor-pointer items-start justify-between gap-3 rounded-xl border border-slate-200 p-4 transition hover:border-emerald-300 dark:border-[#29476b]"
          >
            <div>
              <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ opt.label }}</p>
              <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ opt.desc }}</p>
            </div>
            <input type="checkbox" v-model="form[opt.key]" :disabled="!editable" class="mt-1 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
          </label>
        </div>

        <!-- Rekening bank -->
        <div v-if="form.payment_transfer_enabled">
          <div class="mb-3 flex items-center justify-between">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Rekening Bank</p>
            <Button v-if="editable" type="button" variant="secondary" size="sm" @click="addAccount">
              <Plus :size="14" class="mr-1" /> Tambah Rekening
            </Button>
          </div>

          <div v-if="form.bank_accounts.length === 0" class="rounded-xl border border-dashed border-slate-300 p-6 text-center dark:border-[#29476b]">
            <Landmark :size="28" class="mx-auto text-slate-400" />
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Belum ada rekening. Tambahkan agar pelanggan bisa transfer.</p>
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="(acc, i) in form.bank_accounts"
              :key="i"
              class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 p-4 dark:border-[#29476b] sm:grid-cols-[1fr_1fr_1fr_auto] sm:items-end"
            >
              <Input v-model="acc.bank_name" label="Nama Bank" placeholder="BCA" :disabled="!editable" />
              <Input v-model="acc.account_number" label="No. Rekening" placeholder="1234567890" :disabled="!editable" />
              <Input v-model="acc.account_name" label="Atas Nama" placeholder="Nama pemilik" :disabled="!editable" />
              <button
                v-if="editable"
                type="button"
                class="rounded-lg border border-red-200 p-2.5 text-red-500 transition hover:bg-red-50 dark:border-red-500/30"
                @click="removeAccount(i)"
              >
                <Trash2 :size="16" />
              </button>
            </div>
          </div>
          <p v-if="form.errors['bank_accounts.0.bank_name']" class="mt-1 text-xs text-red-500">{{ form.errors['bank_accounts.0.bank_name'] }}</p>
        </div>

        <!-- QRIS -->
        <div v-if="form.payment_qris_enabled">
          <p class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">Gambar QRIS</p>
          <div v-if="qrisPreview" class="relative mb-2 inline-block overflow-hidden rounded-xl border border-slate-200 dark:border-[#29476b]">
            <img :src="qrisPreview" alt="QRIS" class="h-44 w-44 object-contain p-2" />
            <button
              v-if="editable"
              type="button"
              class="absolute right-1 top-1 rounded-lg bg-white/90 p-1 text-red-500 shadow hover:bg-white"
              @click="removeQris"
            >
              <Trash2 :size="12" />
            </button>
          </div>
          <FileUpload v-model="form.qris_image" label="Upload QRIS" :disabled="!editable" @select="onQrisChange" />
        </div>

        <!-- Catatan -->
        <div>
          <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Catatan Pembayaran</label>
          <textarea
            v-model="form.payment_notes"
            rows="3"
            :disabled="!editable"
            placeholder="Instruksi tambahan, misal: Kirim bukti transfer via WhatsApp..."
            class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500 disabled:opacity-60 dark:border-[#29476b] dark:bg-[#0d1e36] dark:text-white"
          ></textarea>
        </div>

        <FormActions>
          <Button v-if="editable" type="submit" :loading="form.processing">Simpan Pembayaran</Button>
        </FormActions>
      </form>
    </DataPanel>
  </EcommerceLayout>
</template>
