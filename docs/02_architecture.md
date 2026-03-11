# System Architecture

## High-Level Flow

External APIs
↓
Data Ingestion Service
↓
Event Normalization
↓
Database Storage
↓
REST API
↓
Frontend Dashboard
↓
Map Visualization

---

## Components

### Frontend (Inertia + Vue.js)

Responsible for:

* rendering map
* displaying events
* filtering results
* operational dashboard UI

Main technologies:

* Vue.js 3 (Composition API)
* Inertia.js
* TypeScript
* Mapbox GL JS
* Tailwind CSS

The frontend is served by Laravel via Inertia.js — there is no separate SPA. Vue pages receive data directly from Laravel controllers as props.

---

### Backend

Responsible for:

* ingesting external APIs
* normalizing event data
* storing events
* exposing REST endpoints

Main technologies:

* Laravel
* Redis cache
* Scheduled jobs

---

### Infrastructure

Deployment stack:

* Laravel Cloud (monolithic application)
* PostgreSQL
* Redis

