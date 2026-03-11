# API Specification

## Base URL

```
/api
```

---

## Get Events

```
GET /api/events
```

Response:

```
[
 {
  "id": 1,
  "type": "earthquake",
  "lat": -33.44,
  "lng": -70.66,
  "severity": 4.6,
  "location": "Chile",
  "timestamp": "2026-03-10T15:20:00Z"
 }
]
```

---

## Filter by type

```
GET /api/events?type=earthquake
```

---

## Filter by bounding box

```
GET /api/events?bbox=minLng,minLat,maxLng,maxLat
```

