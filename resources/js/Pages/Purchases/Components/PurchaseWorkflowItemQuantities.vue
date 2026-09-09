<script setup>
import { PackageOpen } from 'lucide-vue-next'

defineProps({
  items: { type: Array, default: () => [] },
  label: { type: String, default: 'Jumlah' },
  allowZero: { type: Boolean, default: false },
})

const emit = defineEmits(['update'])

const update = (index, value) => {
  emit('update', { index, value: Number(value) || 0 })
}
</script>

<template>
  <section class="overflow-hidden rounded-2xl border border-slate-200 dark:border-[#29476b]">
    <header class="flex items-center gap-2 bg-slate-50 px-4 py-2.5 dark:bg-[#0a1b33]">
      <PackageOpen :size="17" class="text-slate-400" />
      <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Daftar Barang</h3>
    </header>
    <div class="divide-y divide-slate-100 dark:divide-[#29476b]">
      <div
        v-for="(item, index) in items"
        :key="`${item.name}-${index}`"
        class="grid gap-2.5 px-4 py-3 sm:grid-cols-[minmax(0,1fr)_140px] sm:items-center"
      >
        <div>
          <p class="text-sm font-semibold leading-tight text-slate-900 dark:text-white">
            {{ item.name }}
          </p>
          <p class="mt-0.5 text-xs leading-tight text-slate-500">
            Dipesan: {{ item.orderedQty }} {{ item.unit }}
          </p>
        </div>
        <label>
          <span class="mb-1 block text-xs font-medium text-slate-500">{{ label }}</span>
          <div class="flex items-center rounded-xl border border-slate-300 dark:border-[#29476b]">
            <input
              :value="item.quantity"
              type="number"
              :min="allowZero ? 0 : 1"
              :max="item.orderedQty"
              step="1"
              class="min-w-0 flex-1 border-0 bg-transparent py-2 text-right text-sm focus:ring-0 dark:text-white"
              @input="update(index, $event.target.value)"
            />
            <span class="pr-3 text-xs font-medium text-slate-500">{{ item.unit }}</span>
          </div>
        </label>
      </div>
    </div>
  </section>
</template>
