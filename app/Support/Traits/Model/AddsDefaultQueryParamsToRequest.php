<?php

namespace App\Support\Traits\Model;

use Illuminate\Http\Request;

trait AddsDefaultQueryParamsToRequest
{
    /**
     * Merge default ordering and pagination parameters into the request
     * if they are not already present.
     *
     * @param Request $request The request instance.
     * @param string $orderByConstant The name of the static constant for default order by.
     * @param string $orderDirectionConstant The name of the static constant for default order direction.
     * @param string $perPageConstant The name of the static constant for pagination limit.
     *
     * @return void
     */
    public static function addDefaultQueryParamsToRequest(
        Request $request,
        string $orderByConstant = 'DEFAULT_ORDER_BY',
        string $orderDirectionConstant = 'DEFAULT_ORDER_DIRECTION',
        string $perPageConstant = 'DEFAULT_PER_PAGE'
    ): void {

        $defaultParams = [
            'order_by' => static::{$orderByConstant},
            'order_direction' => static::{$orderDirectionConstant},
            'per_page' => static::{$perPageConstant},
        ];

        $request->mergeIfMissing($defaultParams);
    }
}
