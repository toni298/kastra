import { ref, computed, watch } from 'vue'
import axios from 'axios'

export function usePurchasePayment(grandTotalRef) {
  const paymentMethod = ref('transfer_bank')
  const paymentAmount = ref(0)
  const ownerAmount = ref(0)
  const accountBalance = ref(null)
  const useCombinedPayment = ref(false)
  const fetchingBalance = ref(false)

  const accountType = computed(() => {
    if (paymentMethod.value === 'tunai') return 'cash'
    return 'bank'
  })

  const insufficientBalance = computed(
    () => accountBalance.value !== null && accountBalance.value < grandTotalRef.value
  )

  const canUseCombined = computed(() => insufficientBalance.value && grandTotalRef.value > 0)

  const maxCompanyAmount = computed(() => {
    return accountBalance.value === null ? Number.POSITIVE_INFINITY : Math.max(0, accountBalance.value)
  })

  const setPaymentAmount = (value) => {
    const numeric = Number(value) || 0
    paymentAmount.value = Math.min(numeric, maxCompanyAmount.value)
  }

  const clampPaymentAmount = () => {
    if (accountBalance.value === null) return
    paymentAmount.value = Math.min(Number(paymentAmount.value || 0), maxCompanyAmount.value)
  }

  const companyAmount = computed(() => {
    const amount = Number(paymentAmount.value || 0)
    return Math.min(amount, maxCompanyAmount.value)
  })

  const totalCovered = computed(() => companyAmount.value + Number(ownerAmount.value || 0))

  const fetchBalance = async () => {
    if (!paymentMethod.value) {
      accountBalance.value = null
      return
    }
    fetchingBalance.value = true
    try {
      const { data } = await axios.get(route('cash-bank.accounts.search'), {
        params: { type: 'out', search: '' },
      })
      const matching = data.results.filter((item) => item.type === accountType.value)
      accountBalance.value = matching.reduce((sum, item) => sum + Number(item.balance ?? 0), 0)
    } catch {
      accountBalance.value = null
    } finally {
      fetchingBalance.value = false
    }
  }

  const resetForm = () => {
    paymentAmount.value = 0
    ownerAmount.value = 0
    useCombinedPayment.value = false
    fetchBalance()
  }

  watch(grandTotalRef, () => {
    paymentAmount.value = 0
    ownerAmount.value = 0
    useCombinedPayment.value = false
  })

  watch(paymentMethod, resetForm, { immediate: true })

  watch(useCombinedPayment, (enabled) => {
    if (!enabled) {
      ownerAmount.value = 0
    }
  })

  watch(accountBalance, () => {
    clampPaymentAmount()
  })

  const valid = computed(() => {
    if (!paymentMethod.value || grandTotalRef.value <= 0) return false
    return true
  })

  const getPaymentPayload = () => {
    if (useCombinedPayment.value) {
      return {
        paymentMethod: paymentMethod.value,
        paymentAmount: companyAmount.value,
        ownerAmount: Number(ownerAmount.value || 0),
      }
    }
    return {
      paymentMethod: paymentMethod.value,
      paymentAmount: companyAmount.value,
      ownerAmount: 0,
    }
  }

  return {
    paymentMethod,
    paymentAmount,
    setPaymentAmount,
    ownerAmount,
    accountBalance,
    useCombinedPayment,
    fetchingBalance,
    companyAmount,
    maxCompanyAmount,
    totalCovered,
    insufficientBalance,
    canUseCombined,
    valid,
    getPaymentPayload,
  }
}
