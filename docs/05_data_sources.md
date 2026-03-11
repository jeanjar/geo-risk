# Data Sources

## Earthquakes

USGS Earthquake API

https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_hour.geojson

---

## Weather Alerts

NOAA Weather API

https://www.weather.gov/documentation/services-web-api

---

## Wildfires

NASA FIRMS

https://firms.modaps.eosdis.nasa.gov/

---

## Example Event Structure

```
{
  "id": "evt_001",
  "type": "earthquake",
  "lat": -33.44,
  "lng": -70.66,
  "severity": 4.5,
  "location": "Chile",
  "timestamp": "2026-03-10T15:20:00Z"
}
```

