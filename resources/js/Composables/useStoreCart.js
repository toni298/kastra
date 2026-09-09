import { computed, ref } from 'vue'

const items = ref([])
let hydratedKey = null

function storageKey(store) {
    return `kastra-store-cart:${store?.company_slug}/${store?.branch_slug}`
}

function persist(store) {
    try {
        localStorage.setItem(storageKey(store), JSON.stringify(items.value))
    } catch {
        // storage may be unavailable (private mode); cart still works in-memory
    }
}

export function useStoreCart(store) {
    if (store && hydratedKey !== storageKey(store)) {
        hydratedKey = storageKey(store)
        try {
            items.value = JSON.parse(localStorage.getItem(hydratedKey) || '[]')
        } catch {
            items.value = []
        }
    }

    const count = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0))
    const subtotal = computed(() =>
        items.value.reduce((sum, item) => sum + item.price * item.quantity, 0),
    )

    function add(product, quantity = 1) {
        const existing = items.value.find((item) => item.id === product.id)
        if (existing) {
            existing.quantity = Math.min(existing.quantity + quantity, existing.stock || 9999)
        } else {
            items.value.push({
                id: product.id,
                name: product.name,
                slug: product.slug,
                price: product.price,
                stock: product.stock ?? null,
                image: product.image ?? null,
                quantity,
            })
        }
        persist(store)
    }

    function updateQuantity(id, quantity) {
        const item = items.value.find((entry) => entry.id === id)
        if (!item) return
        const next = Math.max(1, Math.min(quantity, item.stock || 9999))
        item.quantity = next
        persist(store)
    }

    function remove(id) {
        items.value = items.value.filter((item) => item.id !== id)
        persist(store)
    }

    function clear() {
        items.value = []
        persist(store)
    }

    return { items, count, subtotal, add, updateQuantity, remove, clear }
}
