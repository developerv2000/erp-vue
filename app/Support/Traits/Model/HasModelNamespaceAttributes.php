<?php

namespace App\Support\Traits\Model;

trait HasModelNamespaceAttributes
{
    /**
     * Get the fully qualified class name of the model.
     *
     * @return string
     */
    public function getModelNamespaceAttribute(): string
    {
        return static::class;
    }

    /**
     * Get the base class name of the model.
     *
     * @return string
     */
    public static function getBaseModelClassAttribute(): string
    {
        return class_basename(static::class);
    }
}
