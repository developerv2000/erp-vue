<?php

namespace App\Models;

use App\Support\Helpers\GeneralHelper;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
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
     * Get the parent commentable model.
     *
     * This defines a polymorphic relationship where a comment can belong to any model.
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($record) {
            $record->created_at = $record->created_at ?: now();
        });

        static::created(function ($record) {
            if ($record->commentable_type == Manufacturer::class) {
                $record->commentable->updateSelfOnCommentCreate();
            }

            if ($record->commentable_type == Process::class) {
                // Recalculate 'overdue_days' of process after creating comment.
                $record->commentable->recalculateOverdueDays();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Additional attributes
    |--------------------------------------------------------------------------
    */

    /**
     * Get plain text without HTML tags.
     *
     * Used on displaying last comment.
     */
    public function getPlainTextAttribute()
    {
        return GeneralHelper::getPlainTextFromStr($this->body);
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    /**
     * Used in comments.index page
     */
    public static function loadMinifiedUsersOfRecords($comments)
    {
        return $comments->load(['user' => function ($query) {
            $query->select('id', 'name', 'photo')
                ->withOnly([]);
        }]);
    }
}
