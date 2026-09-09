<script setup>
const props = defineProps({
  permissions: { type: Array, required: true }, // array of permission names/objects
  max: { type: Number, default: 3 }, // max badges to show before collapsing
  size: { type: String, default: 'md', validator: (v) => ['sm', 'md', 'lg'].includes(v) },
})

const sizeClasses = {
  sm: 'text-xs px-2 py-0.5',
  md: 'text-sm px-2.5 py-1',
  lg: 'text-base px-3 py-1.5',
}

const visiblePermissions = props.permissions.slice(0, props.max)
const hiddenCount = Math.max(0, props.permissions.length - props.max)
</script>

<template>
  <div class="flex flex-wrap gap-2">
    <span
      v-for="(perm, i) in visiblePermissions"
      :key="i"
      :class="`
        inline-flex items-center rounded-full
        bg-emerald-100 text-emerald-800
        dark:bg-emerald-900/30 dark:text-emerald-300
        font-medium
        ${sizeClasses[size]}
      `"
    >
      {{ typeof perm === 'string' ? perm : perm.name || perm.display_name }}
    </span>

    <span
      v-if="hiddenCount > 0"
      :class="`
        inline-flex items-center rounded-full
        bg-slate-100 text-slate-600
        dark:bg-slate-700/30 dark:text-slate-300
        font-medium
        ${sizeClasses[size]}
      `"
    >
      +{{ hiddenCount }} lainnya
    </span>
  </div>
</template>
