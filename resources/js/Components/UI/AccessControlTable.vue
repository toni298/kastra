<script setup>
import { computed } from 'vue'
import { useAccessControl } from '@/Composables/useAccessControl'

/**
 * AccessControlTable - Table dengan automatic permission-based column/action filtering
 *
 * Contoh penggunaan:
 * <AccessControlTable :items="users">
 *   <template #columns="{ item }">
 *     <td>{{ item.name }}</td>
 *     <td v-if="can('users.edit')">Edit allowed</td>
 *   </template>
 *   <template #actions="{ item }">
 *     <button v-if="can('users.edit')">Edit</button>
 *     <button v-if="can('users.delete')">Delete</button>
 *   </template>
 * </AccessControlTable>
 */
const props = defineProps({
  items: { type: Array, required: true },
  striped: { type: Boolean, default: true },
  hover: { type: Boolean, default: true },
})

const { can } = useAccessControl()

const tableClasses = computed(() => ['w-full border-collapse', props.striped && 'striped'])
</script>

<template>
  <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-[#29476b]">
    <table :class="tableClasses" class="w-full">
      <tbody>
        <tr
          v-for="(item, i) in items"
          :key="i"
          :class="[
            'border-b border-slate-200 dark:border-[#29476b]',
            props.striped && i % 2 === 0 && 'bg-slate-50 dark:bg-[#0a1b33]',
            props.hover && 'hover:bg-slate-100 dark:hover:bg-[#102542]',
          ]"
        >
          <slot :item="item" :can="can" :index="i" />
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
table.striped tbody tr:nth-child(even) {
  background-color: rgb(248 250 252);
}

:dark table.striped tbody tr:nth-child(even) {
  background-color: rgb(10 27 51);
}
</style>
