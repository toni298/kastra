/* global IntersectionObserver */
import { onBeforeUnmount, onMounted, ref } from 'vue'

// AOQ agentOptimationQuery.md baris 61-86:
// - IntersectionObserver (bukan scroll listener)
// - root = container scrollable, rootMargin 160-240px
// - cegah request paralel via loadingMore
// - putuskan observer di onBeforeUnmount
export const useInfiniteScroll = ({
  loadMore,
  hasNext,
  enabled,
  isLoading,
  canLoad = () => true,
}) => {
  const sentinel = ref(null)
  const loadingMore = ref(false)
  let observer = null

  const isActive = () => (typeof enabled === 'function' ? enabled() : true)

  const handleIntersect = (entries) => {
    const entry = entries[0]
    if (!entry?.isIntersecting || loadingMore.value || isLoading?.() || !isActive() || !canLoad())
      return
    if (typeof hasNext === 'function' && !hasNext()) return

    loadingMore.value = true
    Promise.resolve(typeof loadMore === 'function' ? loadMore() : null).finally(() => {
      loadingMore.value = false
    })
  }

  const connect = () => {
    if (!sentinel.value || observer) return
    observer = new IntersectionObserver(handleIntersect, {
      root: sentinel.value.closest('[data-scroll-root]') ?? null,
      rootMargin: '200px',
      threshold: 0,
    })
    observer.observe(sentinel.value)
  }

  const disconnect = () => {
    if (!observer) return
    observer.disconnect()
    observer = null
  }

  const reset = () => {
    disconnect()
    loadingMore.value = false
  }

  onMounted(connect)
  onBeforeUnmount(disconnect)

  return { sentinel, loadingMore, reset, reconnect: connect }
}
