<script setup>
import { computed } from 'vue'
import { useAccessControl } from '@/Composables/useAccessControl'

const props = defineProps({
  permissions: { type: Array, required: true }, // list of permission objects
  selectedPermissions: { type: Array, required: true }, // selected permission IDs
  readOnly: { type: Boolean, default: false },
  columns: { type: Number, default: 3 },
})

const emit = defineEmits(['update:selected'])

const { can } = useAccessControl()

// Group permissions by category
const groupedPermissions = computed(() => {
  const groups = {}
  props.permissions.forEach((perm) => {
    const group = perm.group || 'Lainnya'
    if (!groups[group]) groups[group] = []
    groups[group].push(perm)
  })
  return groups
})

const togglePermission = (permissionId) => {
  if (props.readOnly) return
  const updated = props.selectedPermissions.includes(permissionId)
    ? props.selectedPermissions.filter((id) => id !== permissionId)
    : [...props.selectedPermissions, permissionId]
  emit('update:selected', updated)
}

const isSelected = (permissionId) => props.selectedPermissions.includes(permissionId)
</script>

<template>
  <div class="space-y-6">
    <template v-for="(perms, group) in groupedPermissions" :key="group">
      <div class="space-y-3">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ group }}</h3>
        <div :class="`grid grid-cols-${columns} gap-3`">
          <label
            v-for="perm in perms"
            :key="perm.id"
            class="flex items-start gap-3 rounded-lg border border-slate-200 p-3 cursor-pointer hover:bg-slate-50 dark:border-[#29476b] dark:hover:bg-[#102542]"
          >
            <input
              type="checkbox"
              :checked="isSelected(perm.id)"
              :disabled="readOnly"
              class="mt-1 rounded border-slate-300"
              @change="togglePermission(perm.id)"
            />
            <div class="flex-1">
              <div class="text-sm font-medium text-slate-900 dark:text-white">
                {{ perm.display_name || perm.name }}
              </div>
              <div v-if="perm.description" class="text-xs text-slate-500 dark:text-slate-400">
                {{ perm.description }}
              </div>
            </div>
          </label>
        </div>
      </div>
    </template>
  </div>
</template>
