<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MAD\Services\MADProductSelectionService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MADProductSelectionController extends Controller
{
    /**
     * Generate Excel file for the given model.
     */
    public function generate(string $model): JsonResponse
    {
        $service = new MADProductSelectionService($model);
        $filename = $service->generateExcel();

        return response()->json([
            'filename' => $filename
        ]);
    }

    /**
     * Download previously generated Excel file.
     */
    public function download(string $model, string $filename): BinaryFileResponse
    {
        $service = new MADProductSelectionService($model);

        return $service->downloadExcel($filename);
    }
}
