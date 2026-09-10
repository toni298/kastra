<script setup>
import { ref } from 'vue'
import AppHeader from './Components/AppHeader.vue'
import AppSidebar from './Components/AppSidebar.vue'
import ToastNotifications from '@/Components/Feedback/ToastNotifications.vue'

const sidebarOpen = ref(false)
</script>

<template>
  <div class="min-h-screen bg-slate-50 transition-colors duration-300 dark:bg-[#071426]">
    <AppSidebar :open="sidebarOpen" @close="sidebarOpen = false" />
    <div class="lg:pl-64">
      <AppHeader @menu="sidebarOpen = true">
        <template #title
          ><h1 class="hidden text-xl font-semibold text-slate-950 dark:text-white sm:block">
            <slot name="header">Dashboard</slot>
          </h1></template
        >
      </AppHeader>
      <main class="p-5 lg:p-8"><slot></slot></main>
    </div>
    <ToastNotifications />
  </div>
</template>
