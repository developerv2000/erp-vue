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
}
