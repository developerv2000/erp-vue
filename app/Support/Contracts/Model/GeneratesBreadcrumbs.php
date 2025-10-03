<?php

namespace App\Support\Contracts\Model;

interface GeneratesBreadcrumbs
{
    /**
     * Get the breadcrumb items for the model.
     *
     * Used in routes like 'model.edit', 'comments.index', 'attachments.index' etc.
     */
    public function generateBreadcrumbs($department = null): array;
}
