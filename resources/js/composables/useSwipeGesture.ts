import { onMounted, onUnmounted, type Ref } from 'vue'

interface SwipeOptions {
    onSwipeDown?: () => void
    onSwipeUp?: () => void
    threshold?: number
}

export function useSwipeGesture(elementRef: Ref<HTMLElement | null>, options: SwipeOptions) {
    const threshold = options.threshold ?? 60
    let startY = 0
    let currentY = 0

    function onTouchStart(e: TouchEvent) {
        startY = e.touches[0].clientY
        currentY = startY
    }

    function onTouchMove(e: TouchEvent) {
        currentY = e.touches[0].clientY
    }

    function onTouchEnd() {
        const diff = currentY - startY
        if (Math.abs(diff) >= threshold) {
            if (diff > 0 && options.onSwipeDown) {
                options.onSwipeDown()
            } else if (diff < 0 && options.onSwipeUp) {
                options.onSwipeUp()
            }
        }
    }

    onMounted(() => {
        const el = elementRef.value
        if (!el) return
        el.addEventListener('touchstart', onTouchStart, { passive: true })
        el.addEventListener('touchmove', onTouchMove, { passive: true })
        el.addEventListener('touchend', onTouchEnd, { passive: true })
    })

    onUnmounted(() => {
        const el = elementRef.value
        if (!el) return
        el.removeEventListener('touchstart', onTouchStart)
        el.removeEventListener('touchmove', onTouchMove)
        el.removeEventListener('touchend', onTouchEnd)
    })
}
