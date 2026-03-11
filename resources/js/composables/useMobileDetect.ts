import { ref, onMounted, onUnmounted } from 'vue'

function getInitialValue(breakpoint: number): boolean {
    if (typeof window === 'undefined') return false
    return window.innerWidth < breakpoint
}

export function useMobileDetect(breakpoint = 1024) {
    const isMobile = ref(getInitialValue(breakpoint))
    let mql: MediaQueryList | null = null

    function update(e: MediaQueryListEvent) {
        isMobile.value = e.matches
    }

    onMounted(() => {
        mql = window.matchMedia(`(max-width: ${breakpoint - 1}px)`)
        isMobile.value = mql.matches
        mql.addEventListener('change', update)
    })

    onUnmounted(() => {
        mql?.removeEventListener('change', update)
    })

    return { isMobile }
}
