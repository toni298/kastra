<script setup>
import { ref } from 'vue'
import AccountingConfigurationDetail from './AccountingConfigurationDetail.vue'
import AccountingSettings from './AccountingSettings.vue'
import CoaConfiguration from './CoaConfiguration.vue'

const settingView = ref('center')
</script>

<template>
  <AccountingSettings v-if="settingView === 'center'" @open="settingView = $event" />
  <CoaConfiguration v-else-if="settingView === 'coa'" @back="settingView = 'center'" />
  <AccountingConfigurationDetail v-else :type="settingView" @back="settingView = 'center'" />
</template>
