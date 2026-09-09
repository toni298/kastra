<script setup>
import { computed, defineAsyncComponent, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { BookOpen, Download, FilePlus2, LockKeyhole } from 'lucide-vue-next'
import Button from '@/Components/UI/Button.vue'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import JournalDetailModal from './Components/JournalDetailModal.vue'
import ReportPreviewModal from './Components/ReportPreviewModal.vue'
import ManualJournalModal from './Components/ManualJournalModal.vue'
import ClosePeriodDialog from './Components/ClosePeriodDialog.vue'
import AccountingEmptyState from './Components/AccountingEmptyState.vue'
const props = defineProps({
    hasAccountingData: { type: Boolean, default: true },
    activeTab: { type: String, default: 'overview' },
    overview: { type: Object, default: () => ({}) },
  }),
  modal = ref(null),
  selected = ref(null),
  started = ref(props.hasAccountingData)
const tabs = [
  { id: 'overview', label: 'Overview', route: 'accounting.index' },
  { id: 'journals', label: 'Jurnal', route: 'accounting.journals.index' },
  { id: 'reports', label: 'Laporan', route: 'accounting.reports.index' },
  { id: 'settings', label: 'Pengaturan', route: 'accounting.settings.index' },
]
const panels = {
  overview: defineAsyncComponent(() => import('./Components/AccountingOverview.vue')),
  journals: defineAsyncComponent(() => import('./Components/AccountingJournal.vue')),
  reports: defineAsyncComponent(() => import('./Components/AccountingReports.vue')),
  settings: defineAsyncComponent(() => import('./Components/AccountingSettingsPanel.vue')),
}
const activePanel = computed(() => panels[props.activeTab])
const open = (type, item = null) => {
  modal.value = type
  selected.value = item
}
const close = () => {
  modal.value = null
  selected.value = null
}
</script>
<template>
  <Head title="Akuntansi" /><AuthenticatedLayout
    ><template #header>Akuntansi</template>
    <div class="space-y-5">
      <PageHeader
        title="Akuntansi"
        description="Pantau kesehatan pembukuan, laporan keuangan, dan aktivitas akuntansi yang dibuat secara otomatis oleh Kastra."
        ><template #icon><BookOpen :size="22" /></template
        ><template #actions
          ><div class="flex flex-wrap justify-end gap-2">
            <Button variant="secondary" size="sm" @click="open('manual')"
              ><FilePlus2 :size="16" class="mr-2" />Jurnal Manual</Button
            ><Button variant="secondary" size="sm" @click="open('close-period')"
              ><LockKeyhole :size="16" class="mr-2" />Tutup Periode</Button
            ><Button size="sm"><Download :size="16" class="mr-2" />Export</Button>
          </div></template
        ></PageHeader
      ><AccountingEmptyState v-if="!started" @start="started = true" /><template v-else
        ><nav
          class="accounting-tabs-scroll overflow-x-auto border-b border-slate-200 dark:border-[#29476b]"
        >
          <div class="flex min-w-max gap-7 px-1" role="tablist">
            <Link
              v-for="tab in tabs"
              :key="tab.id"
              :href="route(tab.route)"
              preserve-state
              :class="[
                'relative pb-3 text-sm font-medium transition',
                props.activeTab === tab.id
                  ? 'text-emerald-700 dark:text-emerald-300'
                  : 'text-slate-500 hover:text-slate-800 dark:text-slate-400',
              ]"
              :aria-current="props.activeTab === tab.id ? 'page' : undefined"
            >
              {{ tab.label
              }}<span
                v-if="props.activeTab === tab.id"
                class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-emerald-600"
              ></span>
            </Link>
          </div>
        </nav>
        <KeepAlive>
          <component
            :is="activePanel"
            :key="props.activeTab"
            :overview="overview"
            @detail="open('journal-detail', $event)"
            @preview="open('report-preview', $event)"
          />
        </KeepAlive>
      </template>
    </div>
    <ManualJournalModal v-if="modal === 'manual'" @close="close" /><ClosePeriodDialog
      v-if="modal === 'close-period'"
      @close="close" /><JournalDetailModal
      v-if="modal === 'journal-detail'"
      :journal="selected"
      @close="close" /><ReportPreviewModal
      v-if="modal === 'report-preview'"
      :report="selected"
      @close="close"
  /></AuthenticatedLayout>
</template>
<style scoped>
.accounting-tabs-scroll {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.accounting-tabs-scroll::-webkit-scrollbar {
  display: none;
}
</style>
