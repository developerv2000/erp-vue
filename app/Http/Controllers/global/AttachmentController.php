<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Support\Helpers\ModelHelper;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AttachmentController extends Controller
{
    use DestroysModelRecords;

    // Required for DestroysModelRecords trait
    public static $model = Attachment::class;

    public function viewModelAttachments(Request $request)
    {
        // Secure request
        $this->authorizeGates($request->route('attachable_type'));

        // Retrieve record with attachments eagerly loaded and appended title
        $model = ModelHelper::addFullNamespaceToModelBasename(
            $request->route('attachable_type')
        );

        $query = $model::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
            $query->withTrashed();
        }

        $record = $query->with(['attachments'])
            ->findOrFail($request->route('attachable_id'))
            ->append(['title']); // Used on page title

        // Render page
        return Inertia::render('global/pages/attachments/Index', [
            // Refetched after deleting attachments
            'attachments' => $record->attachments,

            // Never refetched again. But lazy load not used because 'attachments' depends on 'record'
            'record' => $record,

            // Lazy loads, never refetched again
            'breadcrumbs' => $record->generateBreadcrumbs('MAD'),
        ]);
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
