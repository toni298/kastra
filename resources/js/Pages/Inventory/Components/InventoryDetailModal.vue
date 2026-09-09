<script setup>
import { computed } from 'vue'
import { ArrowRightLeft, ClipboardList, SlidersHorizontal } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  type: { type: String, required: true },
  item: { type: Object, required: true },
})
const emit = defineEmits(['close'])
const config = computed(() =>
  props.type === 'transfer'
    ? {
        title: 'Detail Transfer',
        description: 'Informasi dan riwayat perpindahan stok.',
        icon: ArrowRightLeft,
      }
    : {
        title: 'Detail Penyesuaian',
        description: 'Informasi koreksi stok yang tercatat.',
        icon: SlidersHorizontal,
      }
)
const details = computed(() =>
  props.type === 'transfer'
    ? [
        ['Nomor Transfer', props.item.number],
        ['Status', props.item.status],
        ['Produk', props.item.details?.map((detail) => `${detail.product} · ${detail.quantity} ${detail.unit}`).join(', ') || 'Tidak ada detail'],
        ['Gudang Asal', props.item.source],
        ['Gudang Tujuan', props.item.destination],
        ['Tanggal', props.item.date],
      ]
    : [
        ['Produk', props.item.product],
        ['Gudang', props.item.warehouse],
        ['Jenis Penyesuaian', props.item.type],
        ['Qty', props.item.qty],
        ['Alasan', props.item.reason],
        ['Tanggal', props.item.date],
      ]
)
</script>
<template>
  <Modal
    :model-value="true"
    :title="config.title"
    :description="config.description"
    size="lg"
    @update:model-value="emit('close')"
  >
    <div
      class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 p-4 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
    >
      <component :is="config.icon" :size="22" /><span class="font-medium"
        >Informasi hanya-baca</span
      >
    </div>
    <dl class="grid gap-4 sm:grid-cols-2">
      <div
        v-for="detail in details"
        :key="detail[0]"
        class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-[#29476b] dark:bg-[#0a1b33]"
      >
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ detail[0] }}</dt>
        <dd class="mt-1.5 text-sm font-medium text-slate-900 dark:text-white">{{ detail[1] }}</dd>
      </div>
    </dl>
    <section v-if="type === 'transfer'" class="mt-5">
      <h3 class="flex items-center gap-2 font-semibold"><ClipboardList :size="18" />Riwayat</h3>
      <ol class="mt-3 space-y-3 border-l-2 border-emerald-100 pl-5">
         <li>
           <Badge :variant="item.variant">{{ item.status }}</Badge>
        </li>
      </ol>
    </section>
    <template #footer><Button @click="emit('close')">Tutup</Button></template>
  </Modal>
</template>
