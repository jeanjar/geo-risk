export interface GeoEvent {
    id: number
    type: string
    lat: number
    lng: number
    location: string | null
    description: string | null
    severity: number | null
    source: string
    timestamp: string
}

export interface DashboardProps {
    events: GeoEvent[]
    eventTypes: string[]
    filters: {
        type: string | null
    }
    stats: {
        total: number
        byType: Record<string, number>
        critical: number
        last24h: number
        avgSeverityByType: Record<string, number>
    }
    criticalEvents: GeoEvent[]
    timeline: Record<string, number>
}
