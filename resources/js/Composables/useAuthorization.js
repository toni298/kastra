import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Client-only authorization checks; never performs a backend request.
 *
 * SSR-safe: uses Array.isArray + optional chaining so it never crashes
 * in Node.js when page props are not fully hydrated yet.
 *
 * Permission checks are based only on the server-provided permission snapshot.
 */
export function useAuthorization() {
  const page = usePage()
  const permissions = computed(() => page.props.auth?.permissions ?? [])

  const can = (permission) => {
    if (!permission) return true
    return Array.isArray(permissions.value) && permissions.value.includes(permission)
  }

  return { permissions, can }
}
