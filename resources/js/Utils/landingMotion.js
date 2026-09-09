const springTransition = (delay = 0) => ({
  type: 'spring',
  stiffness: 170,
  damping: 24,
  mass: 0.7,
  delay,
})

export const revealOnEnter = (delay = 0, distance = 20) => ({
  initial: {
    opacity: 0,
    y: distance,
  },
  enter: {
    opacity: 1,
    y: 0,
    transition: springTransition(delay),
  },
})

export const revealOnScroll = (delay = 0, distance = 24) => ({
  initial: {
    opacity: 0,
    y: distance,
  },
  visibleOnce: {
    opacity: 1,
    y: 0,
    transition: springTransition(delay),
  },
})

export const fadeOnScroll = (delay = 0) => ({
  initial: {
    opacity: 0,
  },
  visibleOnce: {
    opacity: 1,
    transition: {
      duration: 450,
      delay,
    },
  },
})
