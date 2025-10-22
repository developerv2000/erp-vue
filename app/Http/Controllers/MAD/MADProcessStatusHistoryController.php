<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Http\Requests\MAD\ProcessStatusHistoryUpdateRequest;
use App\Models\Process;
use App\Models\ProcessStatus;
use App\Models\ProcessStatusHistory;
use App\Support\Helpers\ControllerHelper;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class MADProcessStatusHistoryController extends Controller
{
    use DestroysModelRecords;

    // Required for DestroysModelRecords trait
    public static $model = ProcessStatusHistory::class;

    public function index($process)
    {
        $process = Process::withRelationsForHistoryPage()
            ->findOrFail($process)
            ->append(['title']); // Used on page title

        return Inertia::render('departments/MAD/pages/process-status-history/Index', [
            // Refetched after updating/deleting history records of process
            'historyRecords' => $process->statusHistory->append('is_active_history'),

            // Lazy loads. Refetched only on locale change
            'allTableHeaders' => fn() => $this->getAllTableHeadersTranslated(),

            // Lazy loads. Never refetched again
            'process' => fn() => $process, // 'historyRecords' depends on 'process'
            'statuses' => fn() => ProcessStatus::all(),
            'breadcrumbs' => fn() => $process->generateBreadcrumbs('MAD'),
        ]);
    }

    /**
     * Ajax request
     */
    public function update(ProcessStatusHistoryUpdateRequest $request, $record)
    {
        $record = ProcessStatusHistory::find($record);
        $record->updateByMADFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    private function getAllTableHeadersTranslated(): Collection
    {
        $headers = collect([
            ['title' => "Record", 'key' => 'edit', 'sortable' => false],
            ['title' => "Status", 'key' => 'status_id', 'sortable' => true],
            ['title' => "status.General", 'key' => 'general_status_name', 'sortable' => false],
            ['title' => "dates.Start date", 'key' => 'start_date', 'sortable' => true],
            ['title' => "dates.End date", 'key' => 'end_date', 'sortable' => true],
            ['title' => "dates.Duration days", 'key' => 'duration_days', 'sortable' => true],
        ]);

        ControllerHelper::translateTableHeadersTitle($headers);

        return $headers;
    }
}
