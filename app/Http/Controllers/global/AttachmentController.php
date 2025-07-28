<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Support\Helpers\ModelHelper;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    use DestroysModelRecords;

    // used in multiple destroy trait
    public static $model = Attachment::class;

    public function viewModelAttachments(Request $request)
    {
        // Secure request
        $this->authorizeGates($request->route('attachable_type'));

        // Retrieve the model and record with attachments eagerly loaded
        $model = ModelHelper::addFullNamespaceToModelBasename(
            $request->route('attachable_type')
        );
        $record = $model::withTrashed()->with(['attachments'])->findOrFail(
            $request->route('attachable_id')
        );

        // Generate breadcrumbs
        $breadcrumbs = $record->generateBreadcrumbs();
        $breadcrumbs[] = [
            'link' => null,
            'text' => __('Attachments'),
        ];
        $breadcrumbs[] = [
            'link' => null,
            'text' => $record->attachments->count() . ' ' . __('records'),
        ];

        return view('global.attachments.index', compact('record', 'breadcrumbs'));
    }

    public function show(Attachment $record)
    {
        $disk = Storage::disk('local');
        $path = 'attachments/' . $record->folder . '/' . $record->filename;

        if (!$disk->exists($path)) {
            abort(404);
        }
        
        $mimeType = $disk->mimeType($path);

        return response()->stream(function () use ($disk, $path) {
            echo $disk->get($path);
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $record->filename . '"',
        ]);
    }

    private function authorizeGates($modelBaseName)
    {
        switch ($modelBaseName) {
            case 'Manufacturer':
                Gate::authorize('edit-MAD-EPP');
                break;
            case 'Product':
                Gate::authorize('edit-MAD-IVP');
                break;
            default:
                abort(404);
        }
    }
}
