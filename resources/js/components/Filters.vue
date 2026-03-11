<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { typeIcons, typeFilterColors as typeColors } from '@/constants/eventTypes'

defineProps<{
    eventTypes: string[]
    activeType: string | null
    stats: Record<string, number>
}>()

function filterByType(type: string | null) {
    router.get('/', type ? { type } : {}, {
        preserveState: true,
        preserveScroll: true,
    })
}
</script>

<template>
    <div id="filters-panel">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Filters</h2>

        <button
            class="w-full text-left px-3 py-2 rounded-lg text-sm mb-2 border transition-colors"
            :class="!activeType
                ? 'bg-white/10 text-white border-white/20'
                : 'bg-gray-800/50 text-gray-400 border-transparent hover:bg-gray-800'"
            @click="filterByType(null)"
        >
            All Events
        </button>

        <button
            v-for="type in eventTypes"
            :key="type"
            class="w-full text-left px-3 py-2 rounded-lg text-sm mb-2 border transition-colors flex items-center justify-between"
            :class="activeType === type
                ? (typeColors[type] || 'bg-white/10 text-white border-white/20')
                : 'bg-gray-800/50 text-gray-400 border-transparent hover:bg-gray-800'"
            @click="filterByType(type)"
        >
            <span>
                <span class="mr-1.5">{{ typeIcons[type] || '📍' }}</span>
                <span class="capitalize">{{ type.replace('_', ' ') }}</span>
            </span>
            <span
                v-if="stats[type]"
                class="text-xs bg-black/30 px-1.5 py-0.5 rounded"
            >
                {{ stats[type] }}
            </span>
        </button>
    </div>
</template>
