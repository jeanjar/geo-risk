import { driver } from 'driver.js'
import 'driver.js/dist/driver.css'

export function useTour() {
    const tourDriver = driver({
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
        steps: [
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
                    description: 'Generate an AI-powered risk summary using Claude. It analyzes all current events and produces a concise intelligence briefing with patterns, clusters, and actionable insights.',
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
        ],
    })

    function startTour() {
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
