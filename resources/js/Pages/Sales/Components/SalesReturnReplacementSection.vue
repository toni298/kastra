<script setup>
import { computed, ref } from 'vue'
import AsyncSelect from '@/Components/UI/AsyncSelect.vue'
import { Plus, Trash2 } from 'lucide-vue-next'

const props = defineProps({
  replacements: { type: Array, required: true },
  branchId: { type: String, required: true },
})

const emit = defineEmits(['add', 'remove', 'update-quantity'])

const asyncSelect = ref(null)
const endpoint = computed(() => route('search.branch-products') + `?branch_id=${props.branchId}`)

const onAdd = (option) => {
  if (!option) return
  if (props.replacements.some((r) => r.product_id === option.id)) return
  emit('add', {
    product_id: option.id,
    product: option.name,
    unit: option.unit,
    quantity: 1,
    unit_price: option.price,
    available_quantity: option.available_quantity,
  })
  asyncSelect.value?.clearItems()
}

const money = (value) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`
</script>

<template>
  <div class="space-y-3">
    <div
      v-if="replacements.length"
      class="overflow-x-auto rounded-xl border border-slate-200 dark:border-[#29476b]"
    >
      <table class="w-full min-w-[520px] text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-[#0a1b33]">
          <tr>
            <th class="px-3 py-2 text-left">Produk</th>
            <th class="px-3 py-2 text-right">Harga</th>
            <th class="px-3 py-2 text-right">Stok</th>
            <th class="px-3 py-2 text-right">Qty</th>
            <th class="px-3 py-2 text-right">Subtotal</th>
            <th class="w-10 px-3 py-2"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-[#29476b]">
          <tr v-for="(item, index) in replacements" :key="item.product_id">
            <td class="px-3 py-2 font-medium dark:text-white">
              <p>{{ item.product }}</p>
              <p class="mt-0.5 text-xs font-normal text-slate-500">{{ item.unit ?? 'Unit' }}</p>
            </td>
            <td class="px-3 py-2 text-right text-slate-500">{{ money(item.unit_price) }}</td>
            <td class="px-3 py-2 text-right text-slate-500">{{ item.available_quantity ?? 0 }}</td>
            <td class="px-3 py-2 text-right">
              <input
                v-model.number="item.quantity"
                type="number"
                min="1"
                :max="item.available_quantity"
                class="w-20 rounded-lg border-slate-300 py-1.5 text-right dark:border-[#29476b] dark:bg-[#0a1b33]"
              />
            </td>
            <td class="px-3 py-2 text-right font-semibold dark:text-white">
              {{ money(item.quantity * item.unit_price) }}
            </td>
            <td class="px-3 py-2 text-center">
              <button
                type="button"
                class="text-red-500 hover:text-red-700"
                @click="emit('remove', index)"
              >
                <Trash2 :size="16" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-else class="text-sm text-slate-400">Belum ada produk pengganti dipilih.</p>

    <AsyncSelect
      ref="asyncSelect"
      :endpoint="endpoint"
      placeholder="Cari produk pengganti..."
      :clearable="true"
      :minimum-input-length="2"
      label="Tambah Produk Pengganti"
      @change="onAdd"
    />
    <button
      type="button"
      class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-emerald-400 hover:text-emerald-600 dark:border-[#29476b] dark:text-slate-300 dark:hover:border-emerald-400/50"
      @click="asyncSelect?.$el?.querySelector('input')?.focus()"
    >
      <Plus :size="16" /> Tambah Produk
    </button>
  </div>
</template>
