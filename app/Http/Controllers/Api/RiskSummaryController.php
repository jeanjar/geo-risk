<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RiskSummary\GroqRiskSummaryService;
use App\Services\RiskSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiskSummaryController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $provider = config('services.ai_provider', 'groq');
        $type = $request->input('type');
        $model = $request->input('model');

        if ($provider === 'anthropic') {
            $summary = app(RiskSummaryService::class)->generate($type);

            return response()->json([
                'summary' => $summary,
                'provider' => 'Anthropic',
                'model' => 'Claude Haiku',
                'generated_at' => now()->toIso8601String(),
            ]);
        }

        $result = app(GroqRiskSummaryService::class)->generate($type, $model);

        return response()->json([
            ...$result,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function models(): JsonResponse
    {
        return response()->json([
            'models' => GroqRiskSummaryService::availableModels(),
            'default' => GroqRiskSummaryService::DEFAULT_MODEL,
        ]);
    }
}
