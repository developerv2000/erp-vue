<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrivateStorageController extends Controller
{
    public function getOrderFile($path): BinaryFileResponse
    {
        $fullPath = storage_path('app/private/orders/' . $path);

        // Check if the file exists
        if (!file_exists($fullPath)) {
            abort(403);
        }

        return response()->file($fullPath);
    }

    public function getOrderProductFile($path): BinaryFileResponse
    {
        $fullPath = storage_path('app/private/order-products/' . $path);

        // Check if the file exists
        if (!file_exists($fullPath)) {
            abort(403);
        }

        return response()->file($fullPath);
    }

    public function getInvoiceFile($path): BinaryFileResponse
    {
        $fullPath = storage_path('app/private/invoices/' . $path);

        // Check if the file exists
        if (!file_exists($fullPath)) {
            abort(403);
        }

        return response()->file($fullPath);
    }

    public function getShipmentFile($path): BinaryFileResponse
    {
        $fullPath = storage_path('app/private/shipments/' . $path);

        // Check if the file exists
        if (!file_exists($fullPath)) {
            abort(403);
        }

        return response()->file($fullPath);
    }
}
