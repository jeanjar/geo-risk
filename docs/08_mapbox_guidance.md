# Mapbox Integration (Vue.js)

## Install

```
npm install mapbox-gl
```

---

## Environment Variable

```
VITE_MAPBOX_TOKEN=your_token
```

---

## Basic Map Setup (Vue Component)

Example `MapView.vue`:

```vue
<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import mapboxgl from 'mapbox-gl'
import 'mapbox-gl/dist/mapbox-gl.css'

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_TOKEN

const mapContainer = ref<HTMLElement | null>(null)
let map: mapboxgl.Map | null = null

onMounted(() => {
    if (!mapContainer.value) return

    map = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/dark-v11',
        center: [0, 0],
        zoom: 2,
    })
})

onUnmounted(() => {
    map?.remove()
})
</script>

<template>
    <div ref="mapContainer" class="h-full w-full" />
</template>
```

---

## Map Features

Recommended features:

* clustering
* marker layers
* popups
* heatmap (optional)
