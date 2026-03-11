import { ref, onMounted, onUnmounted } from 'vue'

export function useMobileDetect(breakpoint = 1024) {
    const isMobile = ref(false)
    let mql: MediaQueryList

    function update(e: MediaQueryListEvent | MediaQueryList) {
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
