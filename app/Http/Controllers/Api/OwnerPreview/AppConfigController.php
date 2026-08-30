<?php

namespace App\Http\Controllers\Api\OwnerPreview;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $settings = AppSetting::query()
            ->whereIn('key', ['public_app_name', 'public_slogan', 'content_version', 'min_app_version'])
            ->pluck('value', 'key');

        return response()->json([
            'app_name' => $settings['public_app_name'] ?? '',
            'slogan' => $settings['public_slogan'] ?? '',
            'content_version' => (int) ($settings['content_version'] ?? 1),
            'min_app_version' => $settings['min_app_version'] ?? '1.0.0',
        ]);
    }
}
