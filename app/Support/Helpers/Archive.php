<?php

namespace App\Support\Helpers;

use App\Models\Atx;
use App\Models\Process;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * Helper class which stores all used custom functions,
 * which may come in handy in the future.
 *
 * @author Bobur Nuridinov
 */
class Archive
{
    /**
     * Detect and dump duplicate Process records
     * based on business uniqueness constraints.
     */
    public static function ddDuplicateProcessIdsByBusinessKey(): void
    {
        $duplicates = Process::query()
            ->select(
                'product_id',
                'country_id',
                'marketing_authorization_holder_id',
                'trademark_en',
                DB::raw('COUNT(*) as duplicates_count'),
                DB::raw('GROUP_CONCAT(id ORDER BY id) as ids')
            )
            ->groupBy(
                'product_id',
                'country_id', // included implicitly by the rule
                'marketing_authorization_holder_id',
                'trademark_en'
            )
            ->having('duplicates_count', '>', 1)
            ->pluck('ids');

        dd($duplicates);
    }

    /**
     * Detect and dump duplicate process records
     * based on (product_id, country_id, marketing_authorization_holder_id).
     */
    public static function ddDuplicateProcessIdsByBusinessKey2(): void
    {
        $duplicates = DB::table('processes')
            ->join('products', 'products.id', '=', 'processes.product_id')
            ->join('manufacturers', 'manufacturers.id', '=', 'products.manufacturer_id')
            ->join('users as analysts', 'analysts.id', '=', 'manufacturers.analyst_user_id')
            ->select(
                'analysts.name as analyst_name',
                DB::raw('GROUP_CONCAT(processes.id ORDER BY processes.id) as ids'),
                DB::raw('COUNT(*) as duplicates_count')
            )
            ->groupBy(
                'analysts.name',
                'processes.product_id',
                'processes.country_id',
                'processes.marketing_authorization_holder_id',
                'processes.trademark_en'
            )
            ->having('duplicates_count', '>', 1)
            ->get()
            ->groupBy('analyst_name')
            ->map(function ($items) {
                // flatten multiple duplicate groups per analyst into one list
                return $items->pluck('ids')->implode(',');
            });

        dd($duplicates);
    }

    /**
     * Detect and dump duplicate product records
     * based on business uniqueness constraints.
     */
    public static function ddDuplicateProductIdsByBusinessKey(): void
    {
        $duplicates = Product::withTrashed()
            ->select(
                'manufacturer_id',
                'inn_id',
                'form_id',
                'dosage',
                'pack',
                'moq',
                'shelf_life_id',
                DB::raw('COUNT(*) as duplicates_count'),
                DB::raw('GROUP_CONCAT(id ORDER BY id) as ids')
            )
            ->groupBy(
                'manufacturer_id',
                'inn_id',
                'form_id',
                'dosage',
                'pack',
                'moq',
                'shelf_life_id'
            )
            ->having('duplicates_count', '>', 1)
            ->pluck('ids');

        dd($duplicates);
    }

    public static function validateProductAtxes(): void
    {
        Product::chunk(1000, function ($products) {
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
