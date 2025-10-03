<?php

namespace App\Support\Helpers;

class ControllerHelper
{
    /**
     * Prepends the 'Deleted at' column to the given headers collection.
     *
     * @param \Illuminate\Support\Collection $headers
     * @return \Illuminate\Support\Collection
     */
    public static function prependTrashPageTableHeaders($headers)
    {
        return $headers->prepend([
            'title'    => trans('dates.Deletion date'),
            'key'      => 'deleted_at',
            'order'    => 0,
            'width'    => 130,
            'visible'  => 1,
            'sortable' => true,
        ]);
    }
}
