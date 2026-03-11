<script setup lang="ts">
import type { GeoEvent } from '@/types'
import { typeIcons } from '@/constants/eventTypes'

const props = withDefaults(defineProps<{
    total: number
    critical: number
    last24h: number
    byType: Record<string, number>
    criticalEvents: GeoEvent[]
    compact?: boolean
}>(), {
    compact: false,
})

const emit = defineEmits<{
    selectEvent: [event: GeoEvent]
}>()
</script>

<template>
    <!-- Compact mobile version -->
    <div v-if="compact" id="stats-bar-mobile" class="flex items-center gap-1.5 bg-gray-900/85 backdrop-blur-sm rounded-xl px-2.5 py-1.5 border border-gray-700/50 shadow-lg overflow-x-auto">
        <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-800/60 rounded-lg shrink-0">
            <div class="text-sm font-bold">{{ total }}</div>
            <div class="text-[9px] text-gray-400 uppercase">Events</div>
        </div>
        <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-800/60 rounded-lg shrink-0">
            <div class="text-sm font-bold text-blue-400">{{ last24h }}</div>
            <div class="text-[9px] text-gray-400 uppercase">24h</div>
        </div>
        <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-800/60 rounded-lg border border-red-500/30 shrink-0">
            <div class="text-sm font-bold text-red-400">{{ critical }}</div>
            <div class="text-[9px] text-gray-400 uppercase">Critical</div>
        </div>
        <div v-if="criticalEvents.length > 0" class="flex items-center gap-1 ml-1 overflow-x-auto">
            <span class="text-[9px] text-red-400 uppercase font-semibold shrink-0 animate-pulse">!</span>
            <button
                v-for="event in criticalEvents.slice(0, 1)"
                :key="event.id"
                class="flex items-center gap-1 px-1.5 py-0.5 bg-red-500/10 border border-red-500/20 rounded text-[10px] text-red-300 shrink-0"
                @click="emit('selectEvent', event)"
            >
                <span>{{ typeIcons[event.type] || '📍' }}</span>
                <span class="truncate max-w-[100px]">{{ event.location || event.description }}</span>
            </button>
        </div>
    </div>

    <!-- Full desktop version -->
    <div v-else id="stats-bar" class="flex items-stretch gap-3 px-4 py-3 border-b border-gray-800 bg-gray-900/80 overflow-x-auto">
        <!-- KPI Cards -->
        <div class="flex items-center gap-2 px-3 py-2 bg-gray-800/60 rounded-lg border border-gray-700/50 shrink-0">
            <div class="text-xl font-bold">{{ total }}</div>
            <div class="text-[10px] text-gray-400 uppercase leading-tight">Total<br>Events</div>
        </div>

        <div class="flex items-center gap-2 px-3 py-2 bg-gray-800/60 rounded-lg border border-gray-700/50 shrink-0">
            <div class="text-xl font-bold text-blue-400">{{ last24h }}</div>
            <div class="text-[10px] text-gray-400 uppercase leading-tight">Last<br>24h</div>
        </div>

        <div class="flex items-center gap-2 px-3 py-2 bg-gray-800/60 rounded-lg border border-red-500/30 shrink-0">
            <div class="text-xl font-bold text-red-400">{{ critical }}</div>
            <div class="text-[10px] text-gray-400 uppercase leading-tight">Critical<br>Alerts</div>
        </div>

        <!-- Separator -->
        <div class="w-px bg-gray-700/50 shrink-0" />

        <!-- Type breakdown mini badges -->
        <div class="flex items-center gap-1.5 flex-wrap">
            <div
                v-for="(count, type) in byType"
                :key="type"
                class="flex items-center gap-1 px-2 py-1 bg-gray-800/40 rounded text-xs text-gray-300 shrink-0"
            >
                <span>{{ typeIcons[type as string] || '📍' }}</span>
                <span class="font-medium">{{ count }}</span>
            </div>
        </div>

        <!-- Separator -->
        <div class="w-px bg-gray-700/50 shrink-0" />

        <!-- Critical alerts ticker -->
        <div v-if="criticalEvents.length > 0" id="critical-alerts" class="flex items-center gap-2 overflow-x-auto min-w-0">
            <span class="text-[10px] text-red-400 uppercase font-semibold tracking-wider shrink-0 animate-pulse">ALERT</span>
            <button
                v-for="event in criticalEvents.slice(0, 3)"
                :key="event.id"
                class="flex items-center gap-1.5 px-2 py-1 bg-red-500/10 border border-red-500/20 rounded text-xs text-red-300 hover:bg-red-500/20 transition-colors shrink-0"
                @click="emit('selectEvent', event)"
            >
                <span>{{ typeIcons[event.type] || '📍' }}</span>
                <span class="truncate max-w-[160px]">{{ event.location || event.description }}</span>
                <span class="text-red-400 font-medium">{{ event.severity }}</span>
            </button>
        </div>
    </div>
</template>
