<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrivateStorageController extends Controller
{
    public function getOrderProductFile(Request $request, $path)
    {
        $fullPath = storage_path('app/private/order-products/' . $path);

        // Check if the file exists
        if (!file_exists($fullPath)) {
            abort(403);
        }

        return response()->file($fullPath);
    }
}
