<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    timeline: Record<string, number>
}>()

const bars = computed(() => {
    const entries = Object.entries(props.timeline)
    const max = Math.max(...entries.map(([, v]) => v), 1)

    return entries.map(([hour, count]) => ({
        hour,
        count,
        height: Math.max((count / max) * 100, 3),
        isMax: count === max && count > 0,
    }))
})

const totalLast24h = computed(() =>
    Object.values(props.timeline).reduce((sum, v) => sum + v, 0)
)
</script>

<template>
    <div id="activity-timeline" class="flex items-end gap-[3px] px-4 py-4 border-t border-gray-800 bg-gray-900/80 h-[110px]">
        <!-- Label -->
        <div class="flex flex-col justify-end shrink-0 mr-3 pb-4">
            <div class="text-[10px] text-gray-500 uppercase tracking-wider whitespace-nowrap">24h Activity</div>
            <div class="text-sm font-semibold text-gray-200">{{ totalLast24h }}</div>
            <div class="text-[10px] text-gray-500">events</div>
        </div>

        <!-- Bars -->
        <div class="flex items-end gap-[3px] flex-1 h-full min-w-0 pb-4">
            <div
                v-for="(bar, i) in bars"
                :key="bar.hour"
                class="group relative flex-1 flex flex-col items-center justify-end h-full min-w-0"
            >
                <div
                    class="w-full rounded-t transition-all duration-200"
                    :class="[
                        bar.isMax
                            ? 'bg-orange-400 group-hover:bg-orange-300 shadow-[0_0_8px_rgba(251,146,60,0.4)]'
                            : bar.count > 0
                                ? 'bg-blue-500/70 group-hover:bg-blue-400'
                                : 'bg-gray-700/30',
                    ]"
                    :style="{ height: bar.height + '%' }"
                />

                <!-- Tooltip -->
                <div class="absolute bottom-full mb-1.5 hidden group-hover:block z-10">
                    <div class="bg-gray-800 border border-gray-700 rounded px-2 py-1 text-[10px] text-gray-200 whitespace-nowrap shadow-lg">
                        {{ bar.hour }} — <span class="font-semibold">{{ bar.count }}</span> events
                    </div>
                </div>

                <!-- Hour label (every 4h) -->
                <div
                    v-if="i % 4 === 0"
                    class="text-[9px] text-gray-500 absolute -bottom-3.5 whitespace-nowrap"
                >
                    {{ bar.hour }}
                </div>
            </div>
        </div>
    </div>
</template>
