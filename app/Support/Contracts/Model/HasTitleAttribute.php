<?php

namespace App\Support\Contracts\Model;

interface HasTitleAttribute
{
    /**
     * Only models without 'title' attribute should implement this interface.
     *
     * Used in routes like 'model.edit', 'comments.index', 'attachments.index' etc.
     */
    public function getTitleAttribute(): string;
}
