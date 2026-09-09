<script setup>
import AccountingLayout from '@/Layouts/AccountingLayout.vue'
import AccountingOverviewContent from '@/Pages/AccountingDashboard/Components/AccountingOverview.vue'
import ClosePeriodDialog from '@/Pages/AccountingDashboard/Components/ClosePeriodDialog.vue'
import ManualJournalModal from '@/Pages/AccountingDashboard/Components/ManualJournalModal.vue'
import { ref } from 'vue'

defineProps({
  overview: { type: Object, default: () => ({}) },
})

const modal = ref(null)

const open = (type) => {
  modal.value = type
}
const close = () => {
  modal.value = null
}
</script>

<template>
  <AccountingLayout active-tab="overview">
    <!-- <template #actions>
      <div class="flex flex-wrap justify-end gap-2">
        <Button variant="secondary" size="sm" @click="open('manual')">
          <FilePlus2 :size="16" class="mr-2" />Jurnal Manual
        </Button>
        <Button variant="secondary" size="sm" @click="open('close-period')">
          <LockKeyhole :size="16" class="mr-2" />Tutup Periode
        </Button>
        <Button size="sm"><Download :size="16" class="mr-2" />Export</Button>
      </div>
    </template> -->

    <AccountingOverviewContent :overview="overview" />
  </AccountingLayout>

  <ManualJournalModal v-if="modal === 'manual'" @close="close" />
  <ClosePeriodDialog v-if="modal === 'close-period'" @close="close" />
</template>
