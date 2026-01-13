<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Support\Helpers\ControllerHelper;
use App\Support\Helpers\ModelHelper;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    use DestroysModelRecords;

    // Required for DestroysModelRecords trait
    public static $model = Attachment::class;

    public function viewModelAttachments(Request $request): Response
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

            // Lazy loads. Refetched only on locale change
            'allTableHeaders' => fn() => $this->getAllTableHeadersTranslated(),

            // Lazy loads. Never refetched again
            'record' => fn() => $record, // 'attachments' depends on 'record'
            'breadcrumbs' => fn() => $record->generateBreadcrumbs('MAD'),
        ]);
    }

    public function show(Attachment $record): StreamedResponse
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

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function authorizeGates($modelBaseName): void
    {
        match ($modelBaseName) {
            'Manufacturer' => Gate::authorize('view-MAD-EPP'),
            'Product' => Gate::authorize('view-MAD-IVP'),
            default => abort(404),
        };
    }

    private function getAllTableHeadersTranslated(): Collection
    {
        $headers = collect([
            ['title' => "files.Name", 'key' => 'filename', 'sortable' => true],
            ['title' => "files.Size", 'key' => 'file_size_in_mb', 'sortable' => true],
            ['title' => "dates.Date of creation", 'key' => 'created_at', 'sortable' => true],
        ]);

        ControllerHelper::translateTableHeadersTitle($headers);

        return $headers;
    }
}
