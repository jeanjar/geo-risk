<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import mapboxgl from 'mapbox-gl'
import 'mapbox-gl/dist/mapbox-gl.css'
import type { GeoEvent } from '@/types'
import { buildH3GeoJson, zoomToResolution } from '@/composables/useH3Grid'
import { typeIcons } from '@/constants/eventTypes'

const props = defineProps<{
    events: GeoEvent[]
    activeType: string | null
}>()

const emit = defineEmits<{
    selectEvent: [event: GeoEvent]
}>()

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_TOKEN || ''

const mapContainer = ref<HTMLElement | null>(null)
let map: mapboxgl.Map | null = null

type ViewMode = 'markers' | 'heatmap' | 'h3'
const viewMode = ref<ViewMode>('markers')

const severityColor = [
    'interpolate', ['linear'], ['get', 'severity'],
    0, '#22c55e',
    2, '#eab308',
    3.5, '#f97316',
    5, '#ef4444',
]

function buildGeoJson() {
    return {
        type: 'FeatureCollection' as const,
        features: props.events.map(e => ({
            type: 'Feature' as const,
            geometry: {
                type: 'Point' as const,
                coordinates: [e.lng, e.lat] as [number, number],
            },
            properties: {
                id: e.id,
                type: e.type,
                severity: e.severity ?? 1,
                location: e.location ?? '',
                description: e.description ?? '',
                timestamp: e.timestamp,
            },
        })),
    }
}

function setupLayers() {
    if (!map) return

    map.addSource('events', {
        type: 'geojson',
        data: buildGeoJson(),
        cluster: true,
        clusterMaxZoom: 14,
        clusterRadius: 50,
    })

    // Unclustered source for heatmap (no clustering)
    map.addSource('events-heatmap', {
        type: 'geojson',
        data: buildGeoJson(),
    })

    // H3 grid source
    map.addSource('h3-grid', {
        type: 'geojson',
        data: { type: 'FeatureCollection', features: [] },
    })

    // Cluster circles
    map.addLayer({
        id: 'clusters',
        type: 'circle',
        source: 'events',
        filter: ['has', 'point_count'],
        paint: {
            'circle-color': [
                'step', ['get', 'point_count'],
                '#3b82f6', 10,
                '#f59e0b', 30,
                '#ef4444',
            ],
            'circle-radius': [
                'step', ['get', 'point_count'],
                18, 10,
                24, 30,
                32,
            ],
            'circle-stroke-width': 2,
            'circle-stroke-color': '#1e293b',
        },
    })

    // Cluster count
    map.addLayer({
        id: 'cluster-count',
        type: 'symbol',
        source: 'events',
        filter: ['has', 'point_count'],
        layout: {
            'text-field': '{point_count_abbreviated}',
            'text-font': ['DIN Pro Medium', 'Arial Unicode MS Bold'],
            'text-size': 13,
        },
        paint: {
            'text-color': '#ffffff',
        },
    })

    // Individual event points
    map.addLayer({
        id: 'unclustered-point',
        type: 'circle',
        source: 'events',
        filter: ['!', ['has', 'point_count']],
        paint: {
            'circle-color': severityColor,
            'circle-radius': 7,
            'circle-stroke-width': 2,
            'circle-stroke-color': '#0f172a',
        },
    })

    // Heatmap layer (hidden by default)
    map.addLayer({
        id: 'heatmap',
        type: 'heatmap',
        source: 'events-heatmap',
        layout: { visibility: 'none' },
        paint: {
            'heatmap-weight': ['interpolate', ['linear'], ['get', 'severity'], 0, 0.2, 5, 1],
            'heatmap-intensity': ['interpolate', ['linear'], ['zoom'], 0, 1, 9, 3],
            'heatmap-radius': ['interpolate', ['linear'], ['zoom'], 0, 15, 9, 40],
            'heatmap-color': [
                'interpolate', ['linear'], ['heatmap-density'],
                0, 'rgba(0,0,0,0)',
                0.2, '#1e40af',
                0.4, '#3b82f6',
                0.6, '#facc15',
                0.8, '#f97316',
                1, '#ef4444',
            ],
            'heatmap-opacity': 0.8,
        },
    })

    // H3 fill layer (hidden by default)
    map.addLayer({
        id: 'h3-fill',
        type: 'fill',
        source: 'h3-grid',
        layout: { visibility: 'none' },
        paint: {
            'fill-color': [
                'interpolate', ['linear'], ['get', 'avgSeverity'],
                0, '#22c55e',
                2, '#eab308',
                3.5, '#f97316',
                5, '#ef4444',
            ],
            'fill-opacity': 0.55,
        },
    })

    // H3 outline layer
    map.addLayer({
        id: 'h3-outline',
        type: 'line',
        source: 'h3-grid',
        layout: { visibility: 'none' },
        paint: {
            'line-color': '#94a3b8',
            'line-width': 0.8,
            'line-opacity': 0.4,
        },
    })

    // H3 label layer
    map.addLayer({
        id: 'h3-label',
        type: 'symbol',
        source: 'h3-grid',
        layout: {
            visibility: 'none',
            'text-field': ['to-string', ['get', 'count']],
            'text-font': ['DIN Pro Medium', 'Arial Unicode MS Bold'],
            'text-size': 12,
        },
        paint: {
            'text-color': '#ffffff',
            'text-halo-color': '#0f172a',
            'text-halo-width': 1,
        },
    })

    // Click on cluster → zoom in
    map.on('click', 'clusters', (e) => {
        const features = map!.queryRenderedFeatures(e.point, { layers: ['clusters'] })
        const clusterId = features[0]?.properties?.cluster_id
        if (clusterId == null) return
        const source = map!.getSource('events') as mapboxgl.GeoJSONSource
        source.getClusterExpansionZoom(clusterId).then((zoom) => {
            map!.easeTo({
                center: (features[0].geometry as GeoJSON.Point).coordinates as [number, number],
                zoom,
            })
        })
    })

    // Click on point → show popup + emit
    map.on('click', 'unclustered-point', (e) => {
        const feature = e.features?.[0]
        if (!feature) return

        const coords = (feature.geometry as GeoJSON.Point).coordinates.slice() as [number, number]
        const p = feature.properties!

        new mapboxgl.Popup({ closeButton: true, maxWidth: '300px' })
            .setLngLat(coords)
            .setHTML(`
                <div class="text-sm">
                    <div class="font-bold capitalize">${p.type}</div>
                    <div class="text-gray-600">${p.location || 'Unknown location'}</div>
                    <div class="mt-1">${p.description || ''}</div>
                    <div class="mt-1 text-xs text-gray-400">${new Date(p.timestamp).toLocaleString()}</div>
                    ${p.severity ? `<div class="mt-1 text-xs">Severity: ${p.severity}</div>` : ''}
                </div>
            `)
            .addTo(map!)

        const event = props.events.find(ev => ev.id === Number(p.id))
        if (event) emit('selectEvent', event)
    })

    // Click on H3 hex → show popup with stats
    map.on('click', 'h3-fill', (e) => {
        const feature = e.features?.[0]
        if (!feature) return

        const p = feature.properties!
        const types = JSON.parse(p.types || '[]') as string[]
        const typeList = types.map(t => `${typeIcons[t] || '📍'} ${t}`).join(', ')

        new mapboxgl.Popup({ closeButton: true, maxWidth: '280px' })
            .setLngLat(e.lngLat)
            .setHTML(`
                <div class="text-sm">
                    <div class="font-bold">${p.count} event${p.count > 1 ? 's' : ''}</div>
                    <div class="text-gray-600 text-xs mt-1">Avg severity: ${p.avgSeverity} &bull; Max: ${p.maxSeverity}</div>
                    <div class="text-gray-500 text-xs mt-1">${typeList}</div>
                </div>
            `)
            .addTo(map!)
    })

    // Cursor
    map.on('mouseenter', 'clusters', () => { map!.getCanvas().style.cursor = 'pointer' })
    map.on('mouseleave', 'clusters', () => { map!.getCanvas().style.cursor = '' })
    map.on('mouseenter', 'unclustered-point', () => { map!.getCanvas().style.cursor = 'pointer' })
    map.on('mouseleave', 'unclustered-point', () => { map!.getCanvas().style.cursor = '' })
    map.on('mouseenter', 'h3-fill', () => { map!.getCanvas().style.cursor = 'pointer' })
    map.on('mouseleave', 'h3-fill', () => { map!.getCanvas().style.cursor = '' })

    // Zoom-adaptive H3 resolution
    map.on('zoomend', () => {
        if (viewMode.value === 'h3') {
            updateH3Source()
        }
    })
}

function updateH3Source() {
    if (!map) return
    const zoom = map.getZoom()
    const resolution = zoomToResolution(zoom)
    const geojson = buildH3GeoJson(props.events, resolution)
    const source = map.getSource('h3-grid') as mapboxgl.GeoJSONSource | undefined
    if (source) source.setData(geojson)
}

const markerLayers = ['clusters', 'cluster-count', 'unclustered-point']
const h3Layers = ['h3-fill', 'h3-outline', 'h3-label']

function setViewMode(mode: ViewMode) {
    if (!map) return
    viewMode.value = mode

    // Hide all optional layers
    markerLayers.forEach(l => map!.setLayoutProperty(l, 'visibility', 'none'))
    map.setLayoutProperty('heatmap', 'visibility', 'none')
    h3Layers.forEach(l => map!.setLayoutProperty(l, 'visibility', 'none'))

    // Show the selected mode
    switch (mode) {
        case 'markers':
            markerLayers.forEach(l => map!.setLayoutProperty(l, 'visibility', 'visible'))
            break
        case 'heatmap':
            map.setLayoutProperty('heatmap', 'visibility', 'visible')
            break
        case 'h3':
            updateH3Source()
            h3Layers.forEach(l => map!.setLayoutProperty(l, 'visibility', 'visible'))
            break
    }
}

function flyToEvent(event: GeoEvent) {
    if (!map) return
    map.flyTo({
        center: [event.lng, event.lat],
        zoom: 10,
        duration: 1500,
    })
}

defineExpose({ flyToEvent })

function updateSource() {
    if (!map) return
    const source = map.getSource('events') as mapboxgl.GeoJSONSource | undefined
    if (source) source.setData(buildGeoJson())

    const heatSource = map.getSource('events-heatmap') as mapboxgl.GeoJSONSource | undefined
    if (heatSource) heatSource.setData(buildGeoJson())

    if (viewMode.value === 'h3') {
        updateH3Source()
    }
}

watch(() => props.events, updateSource, { deep: true })

onMounted(() => {
    if (!mapContainer.value) return

    map = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/dark-v11',
        center: [0, 20],
        zoom: 2,
    })

    map.addControl(new mapboxgl.NavigationControl(), window.innerWidth < 1024 ? 'top-right' : 'top-left')
    map.on('load', setupLayers)
})

onUnmounted(() => {
    map?.remove()
})
</script>

<template>
    <div id="map-container" class="relative h-full w-full">
        <div ref="mapContainer" class="h-full w-full" />

        <!-- View mode toggles -->
        <div id="heatmap-toggle" class="absolute top-14 left-2 lg:top-3 lg:left-14 z-10 flex gap-1.5">
            <button
                class="px-3 py-2 lg:py-1.5 rounded-lg text-xs font-medium border transition-colors shadow-lg min-h-[36px] lg:min-h-0"
                :class="viewMode === 'markers'
                    ? 'bg-blue-500/20 text-blue-300 border-blue-500/40'
                    : 'bg-gray-900/80 text-gray-300 border-gray-700/50 hover:bg-gray-800'"
                @click="setViewMode('markers')"
            >
                Markers
            </button>
            <button
                class="px-3 py-2 lg:py-1.5 rounded-lg text-xs font-medium border transition-colors shadow-lg min-h-[36px] lg:min-h-0"
                :class="viewMode === 'heatmap'
                    ? 'bg-orange-500/20 text-orange-300 border-orange-500/40'
                    : 'bg-gray-900/80 text-gray-300 border-gray-700/50 hover:bg-gray-800'"
                @click="setViewMode('heatmap')"
            >
                Heatmap
            </button>
            <button
                class="px-3 py-2 lg:py-1.5 rounded-lg text-xs font-medium border transition-colors shadow-lg min-h-[36px] lg:min-h-0"
                :class="viewMode === 'h3'
                    ? 'bg-purple-500/20 text-purple-300 border-purple-500/40'
                    : 'bg-gray-900/80 text-gray-300 border-gray-700/50 hover:bg-gray-800'"
                @click="setViewMode('h3')"
            >
                H3 Grid
            </button>
        </div>
    </div>
</template>
