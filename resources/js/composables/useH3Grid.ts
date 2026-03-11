import { latLngToCell, cellToBoundary } from 'h3-js'
import type { GeoEvent } from '@/types'

export interface H3CellProperties {
    h3Index: string
    count: number
    avgSeverity: number
    maxSeverity: number
    types: string[]
}

export function buildH3GeoJson(
    events: GeoEvent[],
    resolution: number,
): GeoJSON.FeatureCollection {
    const cells = new Map<string, { severities: number[]; types: Set<string> }>()

    for (const event of events) {
        const cell = latLngToCell(event.lat, event.lng, resolution)
        if (!cells.has(cell)) {
            cells.set(cell, { severities: [], types: new Set() })
        }
        const data = cells.get(cell)!
        data.severities.push(event.severity ?? 0)
        data.types.add(event.type)
    }

    const features: GeoJSON.Feature[] = []

    for (const [h3Index, data] of cells) {
        const boundary = cellToBoundary(h3Index, true) // true = GeoJSON [lng, lat] order

        // Skip hexagons that cross the antimeridian (lng range > 180°)
        const lngs = boundary.map(c => c[0])
        const lngRange = Math.max(...lngs) - Math.min(...lngs)
        if (lngRange > 180) continue

        // Close the polygon ring
        const ring = [...boundary, boundary[0]]

        const avgSeverity = data.severities.reduce((a, b) => a + b, 0) / data.severities.length
        const maxSeverity = Math.max(...data.severities)

        features.push({
            type: 'Feature',
            geometry: {
                type: 'Polygon',
                coordinates: [ring],
            },
            properties: {
                h3Index,
                count: data.severities.length,
                avgSeverity: Math.round(avgSeverity * 10) / 10,
                maxSeverity,
                types: Array.from(data.types),
            } satisfies H3CellProperties,
        })
    }

    return { type: 'FeatureCollection', features }
}

export function zoomToResolution(zoom: number): number {
    if (zoom <= 2) return 1
    if (zoom <= 4) return 2
    if (zoom <= 6) return 3
    if (zoom <= 8) return 4
    return 5
}
