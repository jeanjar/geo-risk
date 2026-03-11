<script setup lang="ts">
import { typeIcons, typeDotColors } from '@/constants/eventTypes'

defineProps<{
    eventTypes: string[]
    avgSeverityByType: Record<string, number>
}>()

const severityScale = [
    { label: 'Low', value: '0–2', color: '#22c55e' },
    { label: 'Moderate', value: '2–3.5', color: '#eab308' },
    { label: 'High', value: '3.5–4.5', color: '#f97316' },
    { label: 'Critical', value: '4.5–5', color: '#ef4444' },
]

const clusterScale = [
    { label: '< 10', color: '#3b82f6' },
    { label: '10–30', color: '#f59e0b' },
    { label: '30+', color: '#ef4444' },
]
</script>

<template>
    <div id="map-legend" class="absolute bottom-4 left-3 z-10 flex flex-col gap-2">
        <!-- Severity legend -->
        <div class="bg-gray-900/90 backdrop-blur-sm rounded-lg border border-gray-700/50 px-3 py-2.5 shadow-lg">
            <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-2">Severity</div>
            <div class="flex items-center gap-0.5">
                <div
                    v-for="s in severityScale"
                    :key="s.label"
                    class="flex-1 flex flex-col items-center"
                >
                    <div
                        class="w-full h-2 rounded-sm"
                        :style="{ backgroundColor: s.color }"
                    />
                    <span class="text-[8px] text-gray-500 mt-0.5">{{ s.value }}</span>
                </div>
            </div>
            <div class="flex justify-between mt-0.5">
                <span class="text-[8px] text-gray-500">Low</span>
                <span class="text-[8px] text-gray-500">Critical</span>
            </div>
        </div>

        <!-- Cluster legend -->
        <div class="bg-gray-900/90 backdrop-blur-sm rounded-lg border border-gray-700/50 px-3 py-2.5 shadow-lg">
            <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-2">Clusters</div>
            <div class="flex items-center gap-2">
                <div
                    v-for="c in clusterScale"
                    :key="c.label"
                    class="flex items-center gap-1"
                >
                    <div
                        class="w-3 h-3 rounded-full border border-gray-600"
                        :style="{ backgroundColor: c.color }"
                    />
                    <span class="text-[9px] text-gray-400">{{ c.label }}</span>
                </div>
            </div>
        </div>

        <!-- Event types legend -->
        <div class="bg-gray-900/90 backdrop-blur-sm rounded-lg border border-gray-700/50 px-3 py-2.5 shadow-lg">
            <div class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-2">Event Types</div>
            <div class="grid grid-cols-2 gap-x-3 gap-y-1">
                <div
                    v-for="type in eventTypes"
                    :key="type"
                    class="flex items-center gap-1.5"
                >
                    <span class="text-xs">{{ typeIcons[type] || '📍' }}</span>
                    <span class="text-[9px] text-gray-300 capitalize">{{ type.replace('_', ' ') }}</span>
                    <span
                        v-if="avgSeverityByType[type]"
                        class="text-[8px] text-gray-500 ml-auto"
                    >
                        avg {{ avgSeverityByType[type] }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
