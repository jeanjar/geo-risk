<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import MapView from '@/components/MapView.vue'
import MapLegend from '@/components/MapLegend.vue'
import EventFeed from '@/components/EventFeed.vue'
import Filters from '@/components/Filters.vue'
import StatsBar from '@/components/StatsBar.vue'
import ActivityTimeline from '@/components/ActivityTimeline.vue'
import RiskSummary from '@/components/RiskSummary.vue'
import { useTour } from '@/composables/useTour'
import { useMobileDetect } from '@/composables/useMobileDetect'
import { useSwipeGesture } from '@/composables/useSwipeGesture'
import type { GeoEvent, DashboardProps } from '@/types'

const props = defineProps<DashboardProps>()

const selectedEvent = ref<GeoEvent | null>(null)
const mapRef = ref<InstanceType<typeof MapView> | null>(null)
const showRiskSummary = ref(false)
const { startTour, hasSeenTour, markTourSeen } = useTour()
const { isMobile } = useMobileDetect()

// Mobile bottom sheet
const sheetOpen = ref(false)
const activeTab = ref<'feed' | 'filters' | 'timeline'>('feed')
const sheetHandle = ref<HTMLElement | null>(null)

useSwipeGesture(sheetHandle, {
    onSwipeDown: () => { sheetOpen.value = false },
    onSwipeUp: () => { sheetOpen.value = true },
})

function onSelectEvent(event: GeoEvent) {
    selectedEvent.value = event
    mapRef.value?.flyToEvent(event)
    if (isMobile.value) sheetOpen.value = false
}

function onTabClick(tab: 'feed' | 'filters' | 'timeline') {
    if (activeTab.value === tab && sheetOpen.value) {
        sheetOpen.value = false
    } else {
        activeTab.value = tab
        sheetOpen.value = true
    }
}

function handleStartTour() {
    markTourSeen()
    startTour()
}

// MapLegend ref for tour control
const legendRef = ref<InstanceType<typeof MapLegend> | null>(null)

// Tour action handler — listens to events from useTour composable
function onTourAction(e: Event) {
    const { action, detail } = (e as CustomEvent).detail
    switch (action) {
        case 'open-tab':
            activeTab.value = detail as 'feed' | 'filters' | 'timeline'
            sheetOpen.value = true
            break
        case 'close-sheet':
            sheetOpen.value = false
            break
        case 'expand-legend':
            legendRef.value?.expand()
            break
        case 'collapse-legend':
            legendRef.value?.collapse()
            break
    }
}

onMounted(() => {
    document.addEventListener('tour:action', onTourAction)

    if (!hasSeenTour()) {
        setTimeout(() => {
            handleStartTour()
        }, 1000)
    }
})

onUnmounted(() => {
    document.removeEventListener('tour:action', onTourAction)
})
</script>

<template>
    <div class="flex flex-col h-dvh bg-gray-950 text-white">
        <!-- Top: Stats Bar (desktop only) -->
        <StatsBar
            v-if="!isMobile"
            :total="props.stats.total"
            :critical="props.stats.critical"
            :last24h="props.stats.last24h"
            :by-type="props.stats.byType"
            :critical-events="props.criticalEvents"
            @select-event="onSelectEvent"
        />

        <!-- Middle: Map + Sidebar -->
        <div :class="isMobile ? 'relative flex-1 min-h-0' : 'flex flex-1 min-h-0'">
            <!-- Map (full screen on mobile, flex-1 on desktop) -->
            <div :class="isMobile ? 'absolute inset-0' : 'relative flex-1'">
                <MapView
                    ref="mapRef"
                    :events="props.events"
                    :active-type="props.filters.type"
                    @select-event="onSelectEvent"
                />

                <MapLegend
                    ref="legendRef"
                    :event-types="props.eventTypes"
                    :avg-severity-by-type="props.stats.avgSeverityByType"
                />
            </div>

            <!-- Mobile: Floating header -->
            <div v-if="isMobile" class="absolute top-0 inset-x-0 z-20 flex items-center justify-between px-3 py-2 bg-gradient-to-b from-gray-950/90 via-gray-950/60 to-transparent">
                <div>
                    <h1 class="text-sm font-bold tracking-tight">Global Risk Intelligence</h1>
                    <p class="text-[10px] text-gray-500">Real-time geospatial monitoring</p>
                </div>
                <div class="flex items-center gap-1">
                    <a
                        id="linkedin-link-mobile"
                        href="https://www.linkedin.com/in/jeanjar/"
                        target="_blank"
                        class="p-2 rounded-lg text-gray-400 hover:text-blue-400 hover:bg-gray-800/50 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                    <button
                        class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800/50 transition-colors"
                        @click="handleStartTour"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile: Floating stats (compact) -->
            <div v-if="isMobile" class="absolute top-12 inset-x-0 z-10 px-2">
                <StatsBar
                    :total="props.stats.total"
                    :critical="props.stats.critical"
                    :last24h="props.stats.last24h"
                    :by-type="props.stats.byType"
                    :critical-events="props.criticalEvents"
                    :compact="true"
                    @select-event="onSelectEvent"
                />
            </div>

            <!-- Mobile: AI Briefing FAB -->
            <button
                v-if="isMobile"
                id="ai-briefing-button-mobile"
                class="fixed bottom-[4.5rem] right-3 z-30 w-12 h-12 rounded-full bg-purple-500/90 shadow-lg shadow-purple-500/30 flex items-center justify-center cursor-pointer active:scale-95 transition-transform"
                @click="showRiskSummary = true"
            >
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            </button>

            <!-- Mobile: Bottom Sheet -->
            <div
                v-if="isMobile"
                class="fixed inset-x-0 bottom-0 z-30 max-h-[75vh] rounded-t-2xl bg-gray-900/95 backdrop-blur-lg border-t border-gray-700/50 transform transition-transform duration-300 ease-out safe-bottom"
                :class="sheetOpen ? 'translate-y-0' : 'translate-y-[calc(100%-3rem)]'"
            >
                <!-- Drag handle -->
                <div
                    ref="sheetHandle"
                    class="flex justify-center pt-2 pb-1 cursor-grab active:cursor-grabbing"
                    @click="sheetOpen = !sheetOpen"
                >
                    <div class="w-10 h-1 rounded-full bg-gray-600" />
                </div>

                <!-- Tab bar -->
                <div class="flex border-b border-gray-800">
                    <button
                        class="flex-1 py-2 text-xs font-medium text-center transition-colors"
                        :class="activeTab === 'feed' ? 'text-white border-b-2 border-blue-500' : 'text-gray-500'"
                        @click="onTabClick('feed')"
                    >
                        Events ({{ props.events.length }})
                    </button>
                    <button
                        class="flex-1 py-2 text-xs font-medium text-center transition-colors"
                        :class="activeTab === 'filters' ? 'text-white border-b-2 border-blue-500' : 'text-gray-500'"
                        @click="onTabClick('filters')"
                    >
                        Filters
                    </button>
                    <button
                        class="flex-1 py-2 text-xs font-medium text-center transition-colors"
                        :class="activeTab === 'timeline' ? 'text-white border-b-2 border-blue-500' : 'text-gray-500'"
                        @click="onTabClick('timeline')"
                    >
                        Timeline
                    </button>
                </div>

                <!-- Tab content -->
                <div class="overflow-y-auto" style="max-height: calc(75vh - 5rem);">
                    <div v-show="activeTab === 'feed'" class="p-3">
                        <EventFeed
                            :events="props.events"
                            :selected-event="selectedEvent"
                            @select-event="onSelectEvent"
                        />
                    </div>
                    <div v-show="activeTab === 'filters'" class="p-3">
                        <Filters
                            :event-types="props.eventTypes"
                            :active-type="props.filters.type"
                            :stats="props.stats.byType"
                        />
                    </div>
                    <div v-show="activeTab === 'timeline'" class="p-3">
                        <ActivityTimeline :timeline="props.timeline" />
                    </div>
                </div>
            </div>

            <!-- Desktop: Sidebar -->
            <aside v-if="!isMobile" class="flex w-80 border-l border-gray-800 flex-col bg-gray-900/50">
                <div class="p-4 border-b border-gray-800 flex items-center justify-between">
                    <div>
                        <h1 class="text-lg font-bold tracking-tight">Global Risk Intelligence</h1>
                        <p class="text-xs text-gray-500 mt-0.5">Real-time geospatial event monitoring</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <a
                            id="linkedin-link"
                            href="https://www.linkedin.com/in/jeanjar/"
                            target="_blank"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-blue-400 hover:bg-gray-800 transition-colors"
                            title="LinkedIn"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        <button
                            id="tour-button"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors"
                            title="Take a tour"
                            @click="handleStartTour"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-4 py-3 border-b border-gray-800">
                    <button
                        id="ai-briefing-button"
                        class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-purple-500/10 border border-purple-500/30 text-purple-300 hover:bg-purple-500/20 hover:border-purple-500/50 transition-all group cursor-pointer"
                        @click="showRiskSummary = true"
                    >
                        <div class="w-7 h-7 rounded-md bg-purple-500/20 flex items-center justify-center shrink-0 group-hover:bg-purple-500/30 transition-colors">
                            <svg class="w-3.5 h-3.5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-semibold">AI Risk Briefing</div>
                            <div class="text-[10px] text-purple-400/60">Generate intelligence summary</div>
                        </div>
                        <svg class="w-3.5 h-3.5 ml-auto text-purple-400/40 group-hover:text-purple-400/70 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 border-b border-gray-800">
                    <Filters
                        :event-types="props.eventTypes"
                        :active-type="props.filters.type"
                        :stats="props.stats.byType"
                    />
                </div>

                <div class="p-4 flex-1 min-h-0 overflow-hidden">
                    <EventFeed
                        :events="props.events"
                        :selected-event="selectedEvent"
                        @select-event="onSelectEvent"
                    />
                </div>
            </aside>
        </div>

        <!-- Bottom: Activity Timeline (desktop only) -->
        <ActivityTimeline v-if="!isMobile" :timeline="props.timeline" />

        <!-- AI Risk Summary Modal -->
        <RiskSummary
            v-if="showRiskSummary"
            :active-type="props.filters.type"
            @close="showRiskSummary = false"
        />
    </div>
</template>
