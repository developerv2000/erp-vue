<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Support\Helpers\ModelHelper;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommentController extends Controller
{
    use DestroysModelRecords;

    // used in multiple destroy trait
    public static $model = Comment::class;

    public function index(Request $request)
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
            ->append(['title']);

        // Load minified users of each comments
        Comment::loadMinifiedUsersOfRecords($record->comments);

        // Render page
        return Inertia::render('global/pages/comments/Index', [
            'record' => $record,
            'comments' => $record->comments,
            'commentable_id' => $record->id,
            'commentable_type' => $model,
        ]);
    }

    public function store(Request $request)
    {
        $model = $request->input('commentable_type');
        $recordID = $request->input('commentable_id');

        $recordQuery = $model::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
            $recordQuery->withTrashed();
        }

        $record = $recordQuery->find($recordID);
        $record->addComment($request->input('body'));

        return redirect()->back();
    }

    public function edit(Comment $record)
    {
        $record->load('commentable');

        // Generate breadcrumbs
        $crumbs = $record->commentable->generateBreadcrumbs();
        array_push(
            $crumbs,
            ['link' => null, 'text' => __('Comments')],
            ['link' => null, 'text' => '#' . $record->id]
        );

        return view('global.comments.edit', compact('record', 'crumbs'));
    }

    public function update(Request $request, Comment $record)
    {
        $record->update($request->all());

        return redirect($request->input('previous_url'));
    }
}
