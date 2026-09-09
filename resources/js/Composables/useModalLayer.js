let bodyLockCount = 0
let bodyOverflowBeforeLock = ''
const activeModalTokens = []

export const lockOverlayBodyScroll = () => {
  if (typeof document === 'undefined') return

  if (bodyLockCount === 0) {
    bodyOverflowBeforeLock = document.body.style.overflow
  }

  bodyLockCount += 1
  document.body.style.overflow = 'hidden'
}

export const unlockOverlayBodyScroll = () => {
  if (typeof document === 'undefined') return

  bodyLockCount = Math.max(0, bodyLockCount - 1)

  if (bodyLockCount === 0) {
    document.body.style.overflow = bodyOverflowBeforeLock
    bodyOverflowBeforeLock = ''
  }
}

export const activateOverlayLayer = (token) => {
  const existingIndex = activeModalTokens.lastIndexOf(token)

  if (existingIndex >= 0) {
    activeModalTokens.splice(existingIndex, 1)
  }

  activeModalTokens.push(token)
}

export const deactivateOverlayLayer = (token) => {
  const modalIndex = activeModalTokens.lastIndexOf(token)

  if (modalIndex >= 0) {
    activeModalTokens.splice(modalIndex, 1)
  }
}

export const isTopOverlayLayer = (token) => activeModalTokens.at(-1) === token
