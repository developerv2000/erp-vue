<?php

namespace App\Support\Helpers;

use Illuminate\Support\Str;

class GeneralHelper
{
    /**
     * Get plain text from string without HTML tags.
     */
    public static function getPlainTextFromStr($string)
    {
        if (empty($string)) {
            return '';
        }

        return Str::of($string)
            // Add a space after each closing tag to prevent text from joining
            ->replaceMatches('/>(?!\s)/', '> ')
            // Strip HTML tags
            ->stripTags()
            // Decode HTML entities using PHP's htmlspecialchars_decode()
            ->pipe(fn($str) => htmlspecialchars_decode($str))
            // Replace multiple spaces with a single space
            ->replaceMatches('/\s+/', ' ')
            // Remove spaces before commas and dots
            ->replaceMatches('/\s+([,.])/', '$1')
            // Trim the result
            ->trim();
    }
}
