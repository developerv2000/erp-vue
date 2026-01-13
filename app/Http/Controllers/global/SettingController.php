<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function updateByKey(Request $request): JsonResponse
    {
        $key = $request->route('key');
        $value = $request->route('value', null);

        $availableKeys = [
            'theme',
            'locale',
            'is_leftbar_collapsed',
        ];

        // Check if the key is available
        if (!in_array($key, $availableKeys) || !$value) {
            abort(404);
        }

        auth()->user()->updateSetting($key, $value);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Update table headers including orders, widths, and visibility.
     */
    public function updateTableHeaders(Request $request, $key): JsonResponse
    {
        $user = $request->user();
        $settings = $user->settings ?? [];
        $headersSettings = $settings['table_headers'] ?? [];

        abort_unless(isset($headersSettings[$key]), 404, 'Settings key not found');

        $headers = collect($headersSettings[$key]);
        $requestHeaders = collect($request->input('headers', []))->keyBy('key');

        $updatedHeaders = $headers->map(function ($header) use ($requestHeaders) {
            if ($requestHeaders->has($header['key'])) {
                $incoming = $requestHeaders->get($header['key']);

                $header['order']   = isset($incoming['order']) ? (int) $incoming['order'] : $header['order'];
                $header['width']   = isset($incoming['width']) ? (int) $incoming['width'] : $header['width'];
                $header['visible'] = isset($incoming['visible']) ? (int) $incoming['visible'] : $header['visible'];
            }

            return $header;
        });

        $orderedHeaders = $updatedHeaders->sortBy('order')->values()->all();

        // Save back to user settings
        $headersSettings[$key] = $orderedHeaders;
        $settings['table_headers'] = $headersSettings;
        $user->settings = $settings;
        $user->save();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Reset table headers including orders, widths, and visibility.
     */
    public function resetTableHeaders($key): JsonResponse
    {
        auth()->user()->resetTableHeadersByKey($key);

        return response()->json([
            'success' => true,
        ]);
    }
}
