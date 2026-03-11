# Global Risk Intelligence Dashboard

## Overview

This project is a **full-stack geospatial dashboard** that aggregates real-time global events and visualizes them on an operational map.

The system ingests external APIs such as earthquake feeds and weather alerts, normalizes the data, and exposes it through a backend API that powers a geospatial dashboard.

The goal is to demonstrate the ability to build:

* data-driven platforms
* geospatial dashboards
* full-stack systems
* external data integrations

---

## Core Capabilities

The system will:

* ingest external data sources
* normalize heterogeneous event data
* store events in a database
* expose REST APIs
* visualize events on a geospatial map
* provide filtering and operational dashboards

---

## Example Use Cases

Monitoring events such as:

* earthquakes
* wildfires
* extreme weather
* floods
* operational incidents

---

## Target Tech Stack

Frontend (via Inertia.js)

* Vue.js 3 (Composition API)
* Inertia.js
* TypeScript
* Mapbox GL JS
* Tailwind CSS

Backend

* Laravel 12
* Inertia.js (server-side adapter)
* REST APIs (internal endpoints for map data)
* Redis caching
* Scheduled Jobs (data ingestion)

Infrastructure

* Laravel Cloud (deploy)
* PostgreSQL
* Redis

