<?php

namespace App\Support\Helpers;

use App\Models\Atx;
use App\Models\Inn;
use App\Models\Product;
use App\Models\ProductForm;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Helper class which stores all used custom functions,
 * which may come in handy in the future.
 *
 * @author Bobur Nuridinov
 */
class Archive
{
    public static function validateProductAtxes()
    {
        Product::chunk(500, function ($products) {
            foreach ($products as $product) {
                $atx = Atx::where('inn_id', $product->inn_id)
                    ->where('form_id', $product->form_id)
                    ->first();

                if ($atx) {
                    $product->timestamps = false;
                    $product->atx_id = $atx->id;
                    $product->saveQuietly();
                } else {
                    $product->timestamps = false;
                    $product->atx_id = null;
                    $product->saveQuietly();
                }
            }
        });
    }
}
