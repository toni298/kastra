import { onBeforeUnmount, onMounted, ref } from 'vue'

export function useGeolocation() {
  const coordinates = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const permission = ref('prompt')
  let permissionStatus = null

  const syncPermission = async () => {
    if (!navigator.permissions?.query) return permission.value

    permissionStatus = await navigator.permissions.query({ name: 'geolocation' })
    permission.value = permissionStatus.state
    permissionStatus.onchange = () => {
      permission.value = permissionStatus.state
    }

    return permission.value
  }

  const getCurrentPosition = () =>
    new Promise((resolve) => {
      if (!navigator.geolocation) {
        error.value = 'Geolocation tidak tersedia pada perangkat ini.'
        permission.value = 'unsupported'
        resolve(null)
        return
      }

      loading.value = true
      error.value = null
      navigator.geolocation.getCurrentPosition(
        ({ coords }) => {
          coordinates.value = { latitude: coords.latitude, longitude: coords.longitude }
          permission.value = 'granted'
          loading.value = false
          resolve(coordinates.value)
        },
        (positionError) => {
          error.value = positionError.message
          permission.value = positionError.code === 1 ? 'denied' : permission.value
          loading.value = false
          resolve(null)
        },
        { enableHighAccuracy: true, timeout: 5000, maximumAge: 30000 },
      )
    })

  onMounted(() => syncPermission().then((state) => {
    if (state === 'prompt') getCurrentPosition()
  }))

  onBeforeUnmount(() => {
    if (permissionStatus) permissionStatus.onchange = null
  })

  return { coordinates, loading, error, permission, getCurrentPosition, syncPermission }
}
