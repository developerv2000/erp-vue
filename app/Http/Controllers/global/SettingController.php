<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function updateByKey(Request $request)
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
            return false;
        }

        auth()->user()->updateSetting($key, $value);

        return true;
    }

    /**
     * Update table headers including orders, widths, and visibility.
     */
    public function updateTableHeaders(Request $request, $key)
    {
        $user = $request->user();
        $settings = $user->settings ?? [];
        $tableSettings = $settings['tables'] ?? [];

        abort_unless(isset($tableSettings[$key]), 404, 'Settings key not found');

        $headers = collect($tableSettings[$key]);
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
        $tableSettings[$key] = $orderedHeaders;
        $settings['tables'] = $tableSettings;
        $user->settings = $settings;
        $user->save();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Reset table headers including orders, widths, and visibility.
     */
    public function resetTableHeaders(Request $request, $key)
    {
        auth()->user()->resetSpecificTableHeaders($key);

        return response()->json([
            'success' => true,
        ]);
    }
}
