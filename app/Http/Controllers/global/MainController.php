<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Support\Helpers\FileHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function redirectToHomePage(Request $request): RedirectResponse
    {
        $homePage = $request->user()->detectHomeRouteName();

        return redirect()->to($homePage);
    }

    /**
     * API request
     */
    public function uploadWysiwygImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,gif,webp|max:5120', // 5MB max
        ]);

        $image = $request->file('image');
        $folder = $request->route('folder');
        $uploadPath = 'images/wysiwyg/' . $folder;
        $filename = FileHelper::uploadFile($image, storage_path('app/public/' . $uploadPath));

        return response()->json([
            'url' => url('storage/' . $uploadPath . '/' . $filename),
        ], 200);
    }
}
