<script setup>
import { ref } from 'vue'
import { UserPlus, Users } from 'lucide-vue-next'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'

const props = defineProps({
  customerId: { type: [String, Number], default: null },
  customerEndpoint: { type: String, default: '' },
  selectedBranch: { type: [String, Number], default: '' },
})
const emit = defineEmits(['update:customerId', 'add-customer', 'customer-created'])
const customerSelect = ref(null)
const selectCustomer = (customer) => {
  if (!customer?.id) return

  customerSelect.value?.addItems({
    id: customer.id,
    text: [customer.name, customer.telp].filter(Boolean).join(' '),
  })
  emit('update:customerId', customer.id)
}
const clearCustomerSelection = () => {
  customerSelect.value?.clearItems()
  emit('update:customerId', null)
}
defineExpose({ selectCustomer, clearCustomerSelection })
</script>

<template>
  <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="mb-3 flex items-center justify-between">
      <div>
        <h2 class="text-sm font-bold text-slate-900">
          Pelanggan <span class="font-normal text-slate-400">(Opsional)</span>
        </h2>
      </div>
      <Users :size="17" class="text-blue-500" />
    </div>
    <AsyncSelect
      ref="customerSelect"
      :key="selectedBranch"
      :model-value="customerId"
      :endpoint="customerEndpoint"
      placeholder="Cari pelanggan (nama, email, telepon)..."
      :disabled="!selectedBranch"
      clearable
      @update:model-value="emit('update:customerId', $event)"
    />
    <div class="mt-3 grid grid-cols-2 gap-2">
      <button
        type="button"
        class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-2.5 py-2 text-xs font-bold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100"
        @click="emit('add-customer')"
      >
        <UserPlus :size="15" />Pelanggan Baru
      </button>
      <button
        type="button"
        class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-xl border border-blue-200 bg-white px-2.5 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="!selectedBranch"
        @click="customerSelect?.open()"
      >
        <Users :size="15" />Pilih dari Daftar
      </button>
    </div>
  </section>
</template>
