<script setup>
import { ref } from 'vue'
import AccountingLayout from '@/Layouts/AccountingLayout.vue'
import AccountingConfigurationDetail from '@/Pages/AccountingDashboard/Components/AccountingConfigurationDetail.vue'
import AccountingSettings from '@/Pages/AccountingDashboard/Components/AccountingSettings.vue'
import CoaConfiguration from '@/Pages/AccountingDashboard/Components/CoaConfiguration.vue'

const settingView = ref('center')
</script>

<template>
  <AccountingLayout active-tab="settings">
    <AccountingSettings v-if="settingView === 'center'" @open="settingView = $event" />
    <CoaConfiguration v-else-if="settingView === 'coa'" @back="settingView = 'center'" />
    <AccountingConfigurationDetail v-else :type="settingView" @back="settingView = 'center'" />
  </AccountingLayout>
</template>
