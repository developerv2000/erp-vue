<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    public $timestamps = false;
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Get the parent attachable model (morphTo relationship).
     */
    public function attachable()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($record) {
            $record->created_at = now();
        });

        static::deleting(function ($record) {
            $record->deleteFileFromStorage();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    /**
     * Delete the file associated with the attachment from storage.
     */
    public function deleteFileFromStorage(): void
    {
        $disk = Storage::disk('local');
        $path = 'attachments/' . $this->folder . '/' . $this->filename;

        if ($disk->exists($path)) {
            unlink($disk->path($path));
        }
    }
}
