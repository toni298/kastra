<script setup>
import LandingFooter from './Landing/Components/LandingFooter.vue'
import LandingHeader from './Landing/Components/LandingHeader.vue'
import LandingHero from './Landing/Components/LandingHero.vue'
import ModulesSection from './Landing/Components/ModulesSection.vue'
import SolutionSection from './Landing/Components/SolutionSection.vue'
import WorkflowSection from './Landing/Components/WorkflowSection.vue'

defineProps({
  loginUrl: { type: String, default: '' },
  registerUrl: { type: String, default: '' },
})
</script>

<template>
  <div
    class="min-h-screen overflow-hidden bg-white text-slate-900 transition-colors duration-300 dark:bg-[#081426] dark:text-slate-100"
  >
    <LandingHeader :login-url="loginUrl" :register-url="registerUrl" />
    <main id="top">
      <LandingHero :login-url="loginUrl" :register-url="registerUrl" />
      <SolutionSection />
      <WorkflowSection />
      <ModulesSection />
      <LandingFooter :login-url="loginUrl" :register-url="registerUrl" />
    </main>
  </div>
</template>
