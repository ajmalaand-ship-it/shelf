<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $settings = AppSetting::query()
            ->whereIn('key', ['content_version', 'min_app_version'])
            ->pluck('value', 'key');

        return response()->json([
            'content_version' => (int) ($settings['content_version'] ?? 1),
            'min_app_version' => $settings['min_app_version'] ?? '1.0.0',
        ]);
    }
}
