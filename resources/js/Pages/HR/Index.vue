<script setup>
import { Head } from '@inertiajs/vue3'
import { UsersRound } from 'lucide-vue-next'
import PageHeader from '@/Components/UI/PageHeader.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import HRTabHeader from './Components/HRTabHeader.vue'
import SummaryTab from './Tabs/SummaryTab.vue'
import EmployeesTab from './Tabs/EmployeesTab.vue'
import AttendanceTab from './Tabs/AttendanceTab.vue'
import CommissionOvertimeTab from './Tabs/CommissionOvertimeTab.vue'
import PayrollTab from './Tabs/PayrollTab.vue'

defineProps({
  activeTab: { type: String, default: 'employees' },
  employees: { type: Object, default: null },
  summary: { type: Object, default: () => ({}) },
  branches: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  shifts: { type: Array, default: () => [] },
})
</script>

<template>
  <Head title="SDM / HR" />
  <AuthenticatedLayout>
    <template #header>SDM / HR</template>
    <div class="space-y-5">
      <PageHeader
        title="SDM / HR"
        description="Kelola data karyawan, kompensasi, dan kesiapan operasional tim."
      >
        <template #icon><UsersRound :size="22" /></template>
      </PageHeader>
      <HRTabHeader :active="activeTab" />
      <SummaryTab v-if="activeTab === 'summary'" :summary="summary" />
      <EmployeesTab
        v-else-if="activeTab === 'employees'"
        :employees="employees"
        :branches="branches"
        :shifts="shifts"
        :filters="filters"
      />
      <AttendanceTab v-else-if="activeTab === 'attendance'" />
      <CommissionOvertimeTab v-else-if="activeTab === 'commission'" />
      <PayrollTab v-else />
    </div>
  </AuthenticatedLayout>
</template>
