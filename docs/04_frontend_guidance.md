# Frontend Implementation (Vue.js + Inertia.js)

## Stack

* Vue.js 3 (Composition API)
* Inertia.js
* TypeScript
* Mapbox GL JS
* Tailwind CSS

---

## Core Structure

```
resources/js/
 ├── pages/
 │   └── Dashboard.vue
 ├── components/
 │   ├── MapView.vue
 │   ├── EventFeed.vue
 │   └── Filters.vue
 ├── composables/
 │   └── useEvents.ts
 ├── types/
 │   └── index.ts
 └── app.ts
```

---

## Inertia Setup (app.ts)

```ts
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
})
```

---

## Layout

```
+----------------------+-------------------+
|                      |                   |
|      MAPBOX MAP      |     EVENT FEED    |
|                      |                   |
|                      |      FILTERS      |
+----------------------+-------------------+
```

---

## Data Flow

1. Initial page data (event types, config) arrives via Inertia props from the controller
2. Dynamic map data (events GeoJSON) is fetched via `/api/events` using fetch or axios
3. Filters update query params and re-fetch from the API

---

## Map Features

The map should support:

* event markers
* clustering
* clicking events
* zoom to event
* filtering
