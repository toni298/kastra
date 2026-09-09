<script setup>
import { computed } from 'vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  supplier: { type: Object, required: true },
})

const emit = defineEmits(['close'])

const details = computed(() => [
  ['Kontak Supplier', props.supplier.contact_supplier],
  ['Email', props.supplier.email || '-'],
  ['Alamat', props.supplier.address],
])
</script>

<template>
  <Modal
    :model-value="true"
    :title="supplier.name"
    description="Detail data master supplier"
    size="lg"
    @update:model-value="emit('close')"
  >
    <dl class="grid gap-3 sm:grid-cols-2">
      <div
        v-for="detail in details"
        :key="detail[0]"
        class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]"
      >
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ detail[0] }}</dt>
        <dd class="mt-1 break-words font-semibold text-slate-900 dark:text-white">
          {{ detail[1] }}
        </dd>
      </div>
    </dl>
  </Modal>
</template>
