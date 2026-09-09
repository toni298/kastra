import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * useAccessControl - Composable untuk menu & permission management
 * Menyediakan access control patterns untuk Vue components
 */
export function useAccessControl() {
  const page = usePage()

  const menus = computed(() => page.props.menus ?? [])
  const permissions = computed(() => page.props.auth?.permissions ?? [])
  const user = computed(() => page.props.auth?.user ?? null)

  /**
   * Cek apakah user punya permission tertentu
   * Bypass otomatis untuk role "owner"
   */
  const can = (permission) => {
    if (!permission) return true
    return Array.isArray(permissions.value) && permissions.value.includes(permission)
  }

  /**
   * Cek apakah user punya salah satu dari permissions
   */
  const canAny = (permissionList) => {
    if (!Array.isArray(permissionList) || permissionList.length === 0) return true
    return permissionList.some((perm) => can(perm))
  }

  /**
   * Cek apakah user punya SEMUA permissions
   */
  const canAll = (permissionList) => {
    if (!Array.isArray(permissionList) || permissionList.length === 0) return true
    return permissionList.every((perm) => can(perm))
  }

  /**
   * Dapatkan menu section berdasarkan ID
   */
  const getMenuSection = (sectionId) => {
    return menus.value.find((menu) => menu.id === sectionId)
  }

  /**
   * Dapatkan menu item berdasarkan ID (flat search)
   */
  const getMenuItem = (itemId) => {
    return findMenuItemDeep(menus.value, itemId)
  }

  /**
   * Recursive search untuk menu item
   */
  const findMenuItemDeep = (menuList, itemId) => {
    for (const menu of menuList) {
      if (menu.id === itemId) return menu

      if (menu.items && Array.isArray(menu.items)) {
        const found = findMenuItemDeep(menu.items, itemId)
        if (found) return found
      }

      if (menu.children && Array.isArray(menu.children)) {
        const found = menu.children.find((child) => child.id === itemId)
        if (found) return found
      }
    }

    return null
  }

  /**
   * Filter array items berdasarkan permission
   * Contoh: filterByPermission(items, 'permission') akan return items yang user bisa access
   */
  const filterByPermission = (items, permissionKey = 'permission') => {
    if (!Array.isArray(items)) return []
    return items.filter((item) => can(item[permissionKey]))
  }

  return {
    menus,
    permissions,
    user,
    can,
    canAny,
    canAll,
    getMenuSection,
    getMenuItem,
    filterByPermission,
  }
}
