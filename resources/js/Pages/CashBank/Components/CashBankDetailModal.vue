<script setup>
import { computed } from 'vue'
import { ArrowRightLeft, FileSearch, Landmark, Trash2 } from 'lucide-vue-next'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
const props = defineProps({
  type: { type: String, required: true },
  item: { type: Object, required: true },
})
const emit = defineEmits(['close', 'delete-account'])
const configs = {
  account: {
    title: 'Detail Rekening',
    description: 'Informasi saldo dan identitas rekening perusahaan.',
    icon: Landmark,
  },
  transferDetail: {
    title: 'Detail Transfer Dana',
    description: 'Informasi perpindahan dana antar rekening.',
    icon: ArrowRightLeft,
  },
  reconciliationDetail: {
    title: 'Detail Rekonsiliasi',
    description: 'Perbandingan saldo sistem dan rekening.',
    icon: FileSearch,
  },
}
const config = computed(() => configs[props.type])
const details = computed(() =>
  props.type === 'account'
    ? [
        ['Nama Rekening', props.item.name],
        ['Jenis', props.item.type],
        ['Nomor Rekening', props.item.number],
        ['Mata Uang', props.item.currency],
        ['Saldo Saat Ini', props.item.balance],
        ['Status', props.item.status],
      ]
    : props.type === 'transferDetail'
      ? [
          ['Nomor Transfer', props.item.number],
          ['Rekening Asal', props.item.source],
          ['Rekening Tujuan', props.item.destination],
          ['Nominal', props.item.amount],
          ['Tanggal', props.item.date],
          ['Status', props.item.status],
        ]
      : [
          ['Rekening', props.item.account],
          ['Periode', props.item.period],
          ['Saldo Sistem', props.item.system],
          ['Saldo Bank', props.item.bank],
          ['Selisih', props.item.difference],
          ['Status', props.item.status],
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
    ><div
      class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 p-4 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300"
    >
      <component :is="config.icon" :size="21" /><span class="font-medium"
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
    <section v-if="type === 'transferDetail'" class="mt-5">
      <h3 class="font-semibold dark:text-white">Riwayat Transfer</h3>
      <ol class="mt-3 space-y-3 border-l-2 border-emerald-100 pl-5 dark:border-emerald-400/20">
        <li>
          <p class="text-sm font-medium dark:text-white">Transfer dibuat</p>
          <p class="text-xs text-slate-500">20/07/2026 · 09:10</p>
        </li>
        <li>
          <Badge :variant="item.variant">{{ item.status }}</Badge>
        </li>
      </ol>
    </section>
    <template #footer
      ><Button
        v-if="type === 'account' && item?.account_type !== 'cash'"
        variant="danger"
        @click="emit('delete-account', item)"
        ><Trash2 :size="16" class="mr-2" />Hapus Rekening</Button
      ><Button @click="emit('close')">Tutup</Button></template
    ></Modal
  >
</template>
