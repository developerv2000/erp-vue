<?php

namespace App\Support\Traits\Controller;

use Illuminate\Http\Request;

/**
 * Trait RestoresModelRecords
 *
 * This trait provides functionality to restore model records from trash.
 *
 * **Note:** The parent class using this trait **must declare a public static `$model` variable**,
 * which should hold the fully qualified model class name.
 *
 * @package App\Support\Traits\Controller
 */
trait RestoresModelRecords
{
    public function restore(Request $request)
    {
        // Extract id or ids from request as array to restore through loop
        $ids = (array) ($request->input('id') ?: $request->input('ids'));

        // Restore records
        foreach ($ids as $id) {
            // Check if model exists in trash before restoring
            $model = static::$model::onlyTrashed()->find($id);
            if ($model) {
                $model->restore();
            }
        }

        return response()->json([
            'count' => count($ids),
        ]);
    }
}
