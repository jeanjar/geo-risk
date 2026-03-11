<?php

namespace App\Services;

use App\Services\RiskSummary\RiskContextBuilder;
use Anthropic\Client as AnthropicClient;
use Illuminate\Support\Facades\Cache;

class RiskSummaryService
{
    public function generate(?string $typeFilter = null): string
    {
        $cacheKey = 'risk_summary_anthropic_' . ($typeFilter ?? 'all');

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($typeFilter) {
            $context = RiskContextBuilder::build($typeFilter);

            return $this->callClaude($context);
        });
    }

    private function callClaude(array $context): string
    {
        $apiKey = config('services.anthropic.api_key');

        if (! $apiKey) {
            return 'AI risk summary is not configured. Please set the ANTHROPIC_API_KEY environment variable.';
        }

        $client = new AnthropicClient(apiKey: $apiKey);

        $response = $client->messages->create(
            maxTokens: 500,
            messages: [
                [
                    'role' => 'user',
                    'content' => 'Generate a risk briefing based on this data: ' . json_encode($context),
                ],
            ],
            model: 'claude-haiku-4-5-20251001',
            system: RiskContextBuilder::SYSTEM_PROMPT,
        );

        return $response->content[0]->text ?? 'Unable to generate summary.';
    }
}
