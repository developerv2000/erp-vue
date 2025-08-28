<?php

namespace App\Support\Traits\Model;

use App\Models\Attachment;
use App\Support\Helpers\FileHelper;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

trait HasAttachments
{
    /**
     * Get all of the model's attachments.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Boot the trait and add model events.
     */
    public static function bootHasAttachments()
    {
        static::forceDeleting(function ($model) {
            $model->deleteAttachmentsFolder();
            $model->attachments()->delete(); // Delete attachments quietly
        });
    }

    /**
     * Add an attachment to the model.
     *
     * @param array $attributes
     * @return \App\Models\Attachment
     */
    public function addAttachment(array $attributes)
    {
        return $this->attachments()->create($attributes);
    }

    /**
     * Remove an attachment from the model.
     *
     * @param int $attachmentId
     * @return bool|null
     */
    public function removeAttachment(int $attachmentId)
    {
        return $this->attachments()->find($attachmentId)?->delete();
    }

    /**
     * Store attachments from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function storeAttachmentsFromRequest($request)
    {
        $attachments = $request->file('attachments');

        if ($attachments) {
            $folder = class_basename($this) . '/' . $this->id;
            $storagePath = Storage::disk('local')->path('attachments/' . $folder);

            foreach ($attachments as $attachment) {
                // Get 'file_type' and 'file_size_in_mb' before moving to the store
                $fileType = $attachment->getClientMimeType();
                $fileSizeInMb = round($attachment->getSize() / (1024 * 1024), 2);

                // Move attachment to the store
                $filename = FileHelper::uploadFile($attachment, $storagePath);

                 // Save attachment details in the database
                $this->addAttachment([
                    'filename' => $filename,
                    'folder' => $folder,
                    'file_type' => $fileType,
                    'file_size_in_mb' => $fileSizeInMb
                ]);
            }
        }
    }

    /**
     * Delete the attachments folder with all files inside.
     *
     * @return void
     */
    public function deleteAttachmentsFolder()
    {
        $disk = Storage::disk('local');
        $path = $disk->path('attachments/' . class_basename($this) . "/{$this->id}");

        // Check if the directory exists
        if (is_dir($path)) {
            $files = glob($path . '/*'); // Get all files and folders

            // Recursively delete files and folders
            foreach ($files as $file) {
                is_dir($file) ? rmdir($file) : unlink($file);
            }

            rmdir($path);
        }
    }
}
