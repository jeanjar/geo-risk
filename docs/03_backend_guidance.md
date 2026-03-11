# Backend Implementation (Laravel)

## Responsibilities

The backend service will:

* ingest external APIs
* normalize event data
* store events
* serve pages via Inertia.js (passing data as props to Vue)
* expose internal API endpoints (e.g. GeoJSON for the map)

---

## Suggested Folder Structure

```
app/
 ├── Http
 │   ├── Controllers
 │   │   ├── DashboardController.php      # Inertia pages
 │   │   └── Api
 │   │       └── EventController.php      # JSON API (GeoJSON, filters)
 │   └── Middleware
 ├── Services
 │   ├── EventIngestionService.php
 │   └── EventNormalizationService.php
 ├── Jobs
 │   └── IngestEventsJob.php
 └── Models
     └── Event.php
resources/
 ├── js/
 │   ├── pages/
 │   │   └── Dashboard.vue
 │   ├── components/
 │   │   ├── MapView.vue
 │   │   ├── EventFeed.vue
 │   │   └── Filters.vue
 │   ├── composables/
 │   │   └── useEvents.ts
 │   ├── types/
 │   │   └── index.ts
 │   └── app.ts
 ├── css/
 │   └── app.css
 └── views/
     └── app.blade.php                    # Inertia root template
```

---

## Core Services

### EventIngestionService

Responsible for fetching data from external APIs.

Example sources:

* USGS Earthquake API
* NOAA Weather Alerts
* NASA Fire Data

---

### EventNormalizationService

Converts different API formats into a common schema.

---

## Routes

### Inertia Pages (web.php)

```php
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
```

### API Endpoints (api.php)

Used by the frontend via fetch to dynamically load map data.

```
GET /api/events
GET /api/events?type=earthquake
GET /api/events?bbox=minLng,minLat,maxLng,maxLat
```

### Example Inertia Controller

```php
class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'eventTypes' => Event::distinct()->pluck('type'),
        ]);
    }
}
```

