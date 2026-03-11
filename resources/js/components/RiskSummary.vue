<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { marked } from 'marked'

const props = defineProps<{
    activeType: string | null
}>()

const emit = defineEmits<{
    close: []
}>()

const summary = ref('')
const generatedAt = ref('')
const provider = ref('')
const modelName = ref('')
const loading = ref(false)
const error = ref('')

const availableModels = ref<Record<string, { name: string; hint: string }>>({})
const selectedModel = ref('')

const renderedSummary = computed(() => {
    if (!summary.value) return ''
    return marked.parse(summary.value) as string
})

async function fetchModels() {
    try {
        const { data } = await axios.get('/api/risk-summary/models')
        availableModels.value = data.models
        selectedModel.value = data.default
    } catch {
        // Silently fail, will use default
    }
}

async function fetchSummary() {
    loading.value = true
    error.value = ''
    summary.value = ''

    try {
        const params: Record<string, string> = {}
        if (props.activeType) params.type = props.activeType
        if (selectedModel.value) params.model = selectedModel.value

        const { data } = await axios.get('/api/risk-summary', { params })
        summary.value = data.summary
        provider.value = data.provider
        modelName.value = data.model
        generatedAt.value = new Date(data.generated_at).toLocaleString()
    } catch (e: any) {
        error.value = e.response?.status === 429
            ? 'Rate limit reached. Please wait a moment.'
            : 'Failed to generate risk briefing.'
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    await fetchModels()
    fetchSummary()
})
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" @click.self="emit('close')">
        <div class="bg-gray-900 border border-gray-700/50 rounded-xl shadow-2xl w-full max-w-full lg:max-w-lg mx-3 lg:mx-4 overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white">AI Risk Briefing</h3>
                        <p class="text-[10px] text-gray-500">
                            Powered by {{ provider || 'AI' }} · {{ modelName || 'Loading...' }}
                        </p>
                    </div>
                </div>
                <button
                    class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors"
                    @click="emit('close')"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Model selector -->
            <div v-if="Object.keys(availableModels).length > 1" class="px-5 py-2.5 border-b border-gray-800 bg-gray-900/80">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-gray-500 uppercase tracking-wider font-medium shrink-0">Model</span>
                    <select
                        v-model="selectedModel"
                        class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-2.5 py-1.5 text-xs text-gray-300 focus:outline-none focus:border-purple-500/50"
                    >
                        <option v-for="(model, id) in availableModels" :key="id" :value="id">
                            {{ model.name }} — {{ model.hint }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Body -->
            <div class="px-5 py-4 max-h-[70vh] lg:max-h-[60vh] overflow-y-auto">
                <!-- Loading -->
                <div v-if="loading" class="space-y-3 py-4">
                    <div class="flex items-center gap-2 text-sm text-gray-400">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Analyzing event data...
                    </div>
                    <div class="space-y-2">
                        <div class="h-3 bg-gray-800 rounded animate-pulse w-full" />
                        <div class="h-3 bg-gray-800 rounded animate-pulse w-5/6" />
                        <div class="h-3 bg-gray-800 rounded animate-pulse w-4/6" />
                        <div class="h-3 bg-gray-800 rounded animate-pulse w-full" />
                        <div class="h-3 bg-gray-800 rounded animate-pulse w-3/6" />
                    </div>
                </div>

                <!-- Error -->
                <div v-else-if="error" class="py-4">
                    <div class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3">
                        {{ error }}
                    </div>
                </div>

                <!-- Summary -->
                <div
                    v-else
                    class="prose prose-sm prose-invert max-w-none text-gray-300 leading-relaxed text-sm"
                    v-html="renderedSummary"
                />
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between px-5 py-3 border-t border-gray-800 bg-gray-900/50">
                <span v-if="generatedAt" class="text-[10px] text-gray-500">
                    Generated {{ generatedAt }}
                </span>
                <span v-else />
                <button
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-purple-500/20 text-purple-300 border border-purple-500/30 hover:bg-purple-500/30 transition-colors disabled:opacity-50"
                    :disabled="loading"
                    @click="fetchSummary"
                >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>
</template>
