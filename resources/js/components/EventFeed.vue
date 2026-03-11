<script setup lang="ts">
import type { GeoEvent } from '@/types'
import { typeDotColors as typeColors, typeIcons } from '@/constants/eventTypes'

defineProps<{
    events: GeoEvent[]
    selectedEvent: GeoEvent | null
}>()

const emit = defineEmits<{
    selectEvent: [event: GeoEvent]
}>()

function formatTime(timestamp: string): string {
    const date = new Date(timestamp)
    const now = new Date()
    const diffMs = now.getTime() - date.getTime()
    const diffMin = Math.floor(diffMs / 60000)

    if (diffMin < 1) return 'just now'
    if (diffMin < 60) return `${diffMin}m ago`

    const diffHours = Math.floor(diffMin / 60)
    if (diffHours < 24) return `${diffHours}h ago`

    return date.toLocaleDateString()
}

function severityLabel(severity: number | null): { text: string; class: string } {
    if (severity === null) return { text: 'N/A', class: 'text-gray-500' }
    if (severity >= 4.5) return { text: 'Critical', class: 'text-red-400' }
    if (severity >= 3.5) return { text: 'High', class: 'text-orange-400' }
    if (severity >= 2) return { text: 'Moderate', class: 'text-yellow-400' }
    return { text: 'Low', class: 'text-green-400' }
}

function severityBarWidth(severity: number | null): string {
    if (severity === null) return '0%'
    return `${Math.min((severity / 5) * 100, 100)}%`
}

function severityBarColor(severity: number | null): string {
    if (severity === null) return 'bg-gray-700'
    if (severity >= 4.5) return 'bg-red-500'
    if (severity >= 3.5) return 'bg-orange-500'
    if (severity >= 2) return 'bg-yellow-500'
    return 'bg-green-500'
}
</script>

<template>
    <div id="event-feed" class="flex flex-col h-full min-h-0">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 shrink-0">
            Event Feed
            <span class="text-gray-500 ml-1">({{ events.length }})</span>
        </h2>

        <div v-if="events.length === 0" class="text-gray-500 text-sm py-4 text-center">
            No events found.
        </div>

        <div class="space-y-1.5 overflow-y-auto pr-1 h-full">
            <button
                v-for="event in events"
                :key="event.id"
                class="w-full text-left p-3 lg:p-2.5 rounded-lg transition-colors border min-h-[48px] lg:min-h-0"
                :class="selectedEvent?.id === event.id
                    ? 'bg-white/10 border-white/20'
                    : 'bg-gray-800/30 border-transparent hover:bg-gray-800/60'"
                @click="emit('selectEvent', event)"
            >
                <div class="flex items-start gap-2">
                    <span class="text-sm mt-0.5 shrink-0">{{ typeIcons[event.type] || '📍' }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-medium capitalize truncate">{{ event.type.replace('_', ' ') }}</span>
                            <span class="text-[10px] text-gray-500 shrink-0">{{ formatTime(event.timestamp) }}</span>
                        </div>
                        <div class="text-xs text-gray-400 truncate mt-0.5">
                            {{ event.location || 'Unknown location' }}
                        </div>

                        <!-- Severity bar -->
                        <div v-if="event.severity !== null" class="flex items-center gap-2 mt-1.5">
                            <div class="flex-1 h-1 bg-gray-700/50 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all"
                                    :class="severityBarColor(event.severity)"
                                    :style="{ width: severityBarWidth(event.severity) }"
                                />
                            </div>
                            <span
                                class="text-[10px] font-medium shrink-0"
                                :class="severityLabel(event.severity).class"
                            >
                                {{ event.severity?.toFixed(1) }} · {{ severityLabel(event.severity).text }}
                            </span>
                        </div>
                    </div>
                </div>
            </button>
        </div>
    </div>
</template>
