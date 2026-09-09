<script setup>
import { computed } from 'vue'
import Badge from '@/Components/UI/Badge.vue'
import Modal from '@/Components/UI/Modal.vue'

const props = defineProps({
  product: { type: Object, required: true },
})

const emit = defineEmits(['close'])

const detailRows = computed(() => [
  ['Kategori', props.product.category?.name || '-'],
  ['Brand', props.product.brand?.name || '-'],
  ['Satuan', props.product.unit?.name || '-'],
  ['Harga Jual', `Rp ${new Intl.NumberFormat('id-ID').format(props.product.selling_price)}`],
])

const handleVisibilityChange = (isOpen) => {
  if (!isOpen) {
    emit('close')
  }
}
</script>

<template>
  <Modal
    :model-value="true"
    :title="product.name"
    :description="product.sku || 'Detail produk'"
    size="lg"
    @update:model-value="handleVisibilityChange"
  >
    <dl class="grid gap-3 sm:grid-cols-2">
      <div
        v-for="pair in detailRows"
        :key="pair[0]"
        class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]"
      >
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">
          {{ pair[0] }}
        </dt>
        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ pair[1] }}</dd>
      </div>
      <div class="rounded-xl bg-slate-50 p-4 dark:bg-[#0a1b33]">
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Status</dt>
        <dd class="mt-2">
          <Badge :variant="product.is_active ? 'success' : 'error'">
            {{ product.is_active ? 'Aktif' : 'Nonaktif' }}
          </Badge>
        </dd>
      </div>
    </dl>
  </Modal>
</template>
