import { driver, type DriveStep } from 'driver.js'
import 'driver.js/dist/driver.css'

function isMobile(): boolean {
    return window.innerWidth < 1024
}

function emitTourAction(action: string, detail?: string) {
    document.dispatchEvent(new CustomEvent('tour:action', { detail: { action, detail } }))
}

// Wait for DOM updates after emitting actions
function wait(ms: number): Promise<void> {
    return new Promise(resolve => setTimeout(resolve, ms))
}

function getDesktopSteps(): DriveStep[] {
    return [
        {
            element: '#stats-bar',
            popover: {
                title: 'Overview Stats',
                description: 'Quick glance at total events, activity in the last 24 hours, and critical alerts. Type badges show event distribution at a glance.',
                side: 'bottom',
                align: 'start',
            },
        },
        {
            element: '#critical-alerts',
            popover: {
                title: 'Critical Alerts',
                description: 'Events with severity 4.5+ appear here in real-time. Click any alert to fly to its location on the map.',
                side: 'bottom',
                align: 'center',
            },
        },
        {
            element: '#map-container',
            popover: {
                title: 'Interactive Map',
                description: 'Events are displayed as colored dots based on severity: green (low), yellow (moderate), orange (high), and red (critical). Clusters group nearby events — click to zoom in.',
                side: 'right',
                align: 'center',
            },
        },
        {
            element: '#heatmap-toggle',
            popover: {
                title: 'Visualization Modes',
                description: 'Switch between Markers, Heatmap, and H3 Grid views. The H3 hexagonal grid aggregates events into hexagons — color intensity reflects severity, and each hex shows event count.',
                side: 'bottom',
                align: 'start',
            },
        },
        {
            element: '#map-legend',
            popover: {
                title: 'Map Legend',
                description: 'Reference guide for severity colors, cluster sizes, and event type icons with their average severity levels.',
                side: 'right',
                align: 'end',
            },
        },
        {
            element: '#filters-panel',
            popover: {
                title: 'Event Filters',
                description: 'Filter events by type. The count badge shows how many events exist for each category. Click "All Events" to reset.',
                side: 'left',
                align: 'start',
            },
        },
        {
            element: '#event-feed',
            popover: {
                title: 'Event Feed',
                description: 'Scrollable list of all events sorted by most recent. Each entry shows type, location, time, and a severity bar. Click any event to fly to it on the map.',
                side: 'left',
                align: 'start',
            },
        },
        {
            element: '#activity-timeline',
            popover: {
                title: 'Activity Timeline',
                description: 'Hourly event activity over the last 24 hours. The highlighted bar shows peak activity. Hover over any bar for details.',
                side: 'top',
                align: 'center',
            },
        },
        {
            element: '#ai-briefing-button',
            popover: {
                title: 'AI Risk Briefing',
                description: 'Generate an AI-powered risk summary. It analyzes all current events and produces a concise intelligence briefing with patterns, clusters, and actionable insights.',
                side: 'bottom',
                align: 'end',
            },
        },
        {
            element: '#linkedin-link',
            popover: {
                title: 'Built by Jean Jar',
                description: 'This dashboard was developed by Jean Jar. Connect on LinkedIn to learn more about this project and others.',
                side: 'bottom',
                align: 'end',
            },
        },
        {
            element: '#tour-button',
            popover: {
                title: 'Replay Tour',
                description: 'You can restart this introduction anytime by clicking this button.',
                side: 'bottom',
                align: 'end',
            },
        },
    ]
}

function getMobileSteps(): DriveStep[] {
    return [
        // 1. Map (already visible)
        {
            element: '#map-container',
            popover: {
                title: 'Interactive Map',
                description: 'Events are colored by severity: green (low), yellow (moderate), orange (high), red (critical). Tap clusters to zoom in, tap events for details.',
                side: 'bottom',
                align: 'center',
            },
            onHighlightStarted: () => {
                emitTourAction('close-sheet')
            },
        },
        // 2. Stats (floating on mobile)
        {
            element: '#stats-bar-mobile',
            popover: {
                title: 'Quick Stats',
                description: 'Total events, last 24h activity, and critical alerts at a glance.',
                side: 'bottom',
                align: 'center',
            },
        },
        // 3. View mode toggles
        {
            element: '#heatmap-toggle',
            popover: {
                title: 'Visualization Modes',
                description: 'Switch between Markers, Heatmap, and H3 hexagonal grid views.',
                side: 'bottom',
                align: 'start',
            },
        },
        // 4. Legend (expand it first)
        {
            element: '#map-legend',
            popover: {
                title: 'Map Legend',
                description: 'Tap "Legend" to expand. Shows severity colors, cluster sizes, and event types.',
                side: 'top',
                align: 'start',
            },
            onHighlightStarted: () => {
                emitTourAction('expand-legend')
            },
        },
        // 5. Event Feed (open sheet, switch to feed tab)
        {
            element: '#event-feed',
            popover: {
                title: 'Event Feed',
                description: 'All events sorted by most recent. Tap any event to fly to its location on the map.',
                side: 'top',
                align: 'center',
            },
            onHighlightStarted: async () => {
                emitTourAction('open-tab', 'feed')
                await wait(350)
            },
        },
        // 6. Filters (switch to filters tab)
        {
            element: '#filters-panel',
            popover: {
                title: 'Event Filters',
                description: 'Filter events by type. The badge shows the count for each category.',
                side: 'top',
                align: 'center',
            },
            onHighlightStarted: async () => {
                emitTourAction('open-tab', 'filters')
                await wait(350)
            },
        },
        // 7. Timeline (switch to timeline tab)
        {
            element: '#activity-timeline',
            popover: {
                title: 'Activity Timeline',
                description: '24-hour event activity. The highlighted bar shows peak activity. Tap bars for details.',
                side: 'top',
                align: 'center',
            },
            onHighlightStarted: async () => {
                emitTourAction('open-tab', 'timeline')
                await wait(350)
            },
        },
        // 8. AI FAB
        {
            element: '#ai-briefing-button-mobile',
            popover: {
                title: 'AI Risk Briefing',
                description: 'Tap to generate an AI-powered intelligence summary analyzing all current events.',
                side: 'left',
                align: 'center',
            },
            onHighlightStarted: () => {
                emitTourAction('close-sheet')
                emitTourAction('collapse-legend')
            },
        },
        // 9. LinkedIn
        {
            element: '#linkedin-link-mobile',
            popover: {
                title: 'Built by Jean Jar',
                description: 'Connect on LinkedIn to learn more about this project.',
                side: 'bottom',
                align: 'end',
            },
        },
    ]
}

export function useTour() {
    let tourDriver: ReturnType<typeof driver> | null = null

    function startTour() {
        const steps = isMobile() ? getMobileSteps() : getDesktopSteps()

        tourDriver = driver({
            showProgress: true,
            animate: true,
            overlayColor: 'rgba(148, 163, 184, 0.6)',
            stagePadding: 10,
            stageRadius: 10,
            smoothScroll: true,
            popoverClass: 'georisk-tour',
            nextBtnText: 'Next',
            prevBtnText: 'Back',
            doneBtnText: 'Done',
            steps,
            onDestroyed: () => {
                // Reset UI state when tour ends
                if (isMobile()) {
                    emitTourAction('close-sheet')
                    emitTourAction('collapse-legend')
                }
            },
        })

        tourDriver.drive()
    }

    function hasSeenTour(): boolean {
        return localStorage.getItem('georisk-tour-seen') === 'true'
    }

    function markTourSeen() {
        localStorage.setItem('georisk-tour-seen', 'true')
    }

    return { startTour, hasSeenTour, markTourSeen }
}
