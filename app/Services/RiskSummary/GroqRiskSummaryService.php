<?php

namespace App\Services\RiskSummary;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GroqRiskSummaryService
{
    public const MODELS = [
        'llama-3.3-70b-versatile' => ['name' => 'Llama 3.3 70B', 'hint' => 'Best overall analysis'],
        'llama-3.1-8b-instant' => ['name' => 'Llama 3.1 8B', 'hint' => 'Fastest response'],
        'meta-llama/llama-4-scout-17b-16e-instruct' => ['name' => 'Llama 4 Scout 17B', 'hint' => 'Latest generation'],
        'qwen/qwen3-32b' => ['name' => 'Qwen3 32B', 'hint' => 'Strong reasoning'],
        'moonshotai/kimi-k2-instruct' => ['name' => 'Kimi K2', 'hint' => 'Detailed insights'],
    ];

    public const DEFAULT_MODEL = 'llama-3.3-70b-versatile';

    public function generate(?string $typeFilter = null, ?string $model = null): array
    {
        $model = $model && isset(self::MODELS[$model]) ? $model : self::DEFAULT_MODEL;
        $cacheKey = 'risk_summary_groq_' . ($typeFilter ?? 'all') . '_' . $model;

        $summary = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($typeFilter, $model) {
            $context = RiskContextBuilder::build($typeFilter);

            return $this->callGroq($context, $model);
        });

        return [
            'summary' => $summary,
            'provider' => 'Groq',
            'model' => self::MODELS[$model]['name'] ?? $model,
        ];
    }

    public static function availableModels(): array
    {
        return self::MODELS;
    }

    private function callGroq(array $context, string $model): string
    {
        $apiKey = config('services.groq.api_key');

        if (! $apiKey) {
            return 'AI risk summary is not configured. Please set the GROQ_API_KEY environment variable.';
        }

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'max_tokens' => 500,
                'temperature' => 0.7,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => RiskContextBuilder::SYSTEM_PROMPT,
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Generate a risk briefing based on this data: ' . json_encode($context),
                    ],
                ],
            ]);

        if ($response->failed()) {
            $error = $response->json('error.message', 'Unknown error');

            return "Failed to generate summary: {$error}";
        }

        return $response->json('choices.0.message.content', 'Unable to generate summary.');
    }
}
