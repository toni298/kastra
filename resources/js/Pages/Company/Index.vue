<script setup>
import { Building2 } from 'lucide-vue-next'
import CompanyLayout from '@/Layouts/CompanyLayout.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import FormSectionHeader from '@/Components/UI/FormSectionHeader.vue'
import CompanyProfileForm from './Components/CompanyProfileForm.vue'
import CompanyTaxConfigurationForm from './Components/CompanyTaxConfigurationForm.vue'

defineProps({
  company: { type: Object, required: true },
})
</script>

<template>
  <CompanyLayout active-tab="information">
    <DataPanel>
      <FormSectionHeader
        title="Informasi Perusahaan"
        description="Identitass usaha, legalitas, kontak, dan alamat perusahaan."
      >
        <template #icon><Building2 :size="18" /></template>
      </FormSectionHeader>
      <CompanyProfileForm :company="company" />
    </DataPanel>

    <DataPanel>
      <FormSectionHeader
        title="Pengaturan Pajak"
        description="Konfigurasi pajak perusahaan (PPN, PPh, dll)."
      >
        <template #icon><Building2 :size="18" /></template>
      </FormSectionHeader>
      <CompanyTaxConfigurationForm :company="company" />
    </DataPanel>
  </CompanyLayout>
</template>
