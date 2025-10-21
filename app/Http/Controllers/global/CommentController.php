<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\User;
use App\Support\Helpers\ModelHelper;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommentController extends Controller
{
    use DestroysModelRecords; // AJAX request

    // used in multiple destroy trait
    public static $model = Comment::class;

    public function viewModelComments(Request $request)
    {
        // Retrieve record with comments eagerly loaded and appended title
        $model = ModelHelper::addFullNamespaceToModelBasename(
            $request->route('commentable_type')
        );

        $query = $model::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
            $query->withTrashed();
        }

        $record = $query->with(['comments'])
            ->findOrFail($request->route('commentable_id'))
            ->append(['title']); // Used on page title

        // Load minified users of each comments
        Comment::loadMinifiedUsersOfRecords($record->comments);

        // Render page
        return Inertia::render('global/pages/comments/Index', [
            // Refetched after storing/updating/deleting comments
            'comments' => $record->comments,

            // Lazy loads, never refetched again
            'record' => fn() => $record, // 'comments' depends on 'record'
            'commentable_id' => fn() => $record->id,
            'commentable_type' => fn() => $model,
            'deletedUserImage' => fn() => User::getImageUrlForDeletedUser(),
        ]);
    }

    /**
     * Ajax request
     */
    public function store(Request $request)
    {
        $model = $request->input('commentable_type');
        $recordID = $request->input('commentable_id');

        $recordQuery = $model::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
            $recordQuery->withTrashed();
        }

        $record = $recordQuery->findOrFail($recordID);
        $record->addComment($request->input('body'));

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Ajax request
     */
    public function update(Request $request, Comment $record)
    {
        $record->update($request->only('body'));

        return response()->json([
            'success' => true,
        ]);
    }
}
