<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-secondary-200">
      <thead class="bg-secondary-50">
        <slot name="thead"></slot>
      </thead>
      <tbody class="bg-white divide-y divide-secondary-200">
        <slot name="tbody"></slot>
      </tbody>
    </table>
  </div>
</template>

<script setup>
// Basic table wrapper
</script>
