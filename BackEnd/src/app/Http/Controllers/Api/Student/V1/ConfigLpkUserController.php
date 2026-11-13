<?php

namespace App\Http\Controllers\Api\Student\V1;

use App\Http\Controllers\Controller;
use App\Models\ConfigLpk;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log; 

class ConfigLpkUserController extends Controller
{
    /**
     * Get URL Application.
     *
     * @return JsonResponse
     */
    public function getUrlApp(): JsonResponse
    {
        try {
            // Log::info("TESSSS BANG"); // Logging
            // Ambil data URL dari model ConfigLPk
            $configLpk = ConfigLPk::first();

            if (!$configLpk) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL not found',
                ], 404);
            }

            return response()->json([
                'id' => $configLpk->id,
                'status' => "success",
                'url_android_app' => $configLpk->url_android_app,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching URL: ' . $e->getMessage()); // Logging error

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch URL',
            ], 500);
        }
    }
}
