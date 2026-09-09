<script setup>
import { Image } from 'lucide-vue-next'
import CompanyLayout from '@/Layouts/CompanyLayout.vue'
import DataPanel from '@/Components/UI/DataPanel.vue'
import FormSectionHeader from '@/Components/UI/FormSectionHeader.vue'
import CompanyLogoForm from '../Components/CompanyLogoForm.vue'

defineProps({
  company: { type: Object, required: true },
})
</script>

<template>
  <CompanyLayout active-tab="logo">
    <DataPanel>
      <FormSectionHeader
        title="Logo Perusahaan"
        description="Identitas visual untuk aplikasi dan dokumen resmi."
      >
        <template #icon><Image :size="18" /></template>
      </FormSectionHeader>
      <CompanyLogoForm :company="company" />
    </DataPanel>
  </CompanyLayout>
</template>
