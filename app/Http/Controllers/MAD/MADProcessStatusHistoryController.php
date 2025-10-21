<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\ProcessStatusHistory;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MADProcessStatusHistoryController extends Controller
{
    use DestroysModelRecords;

    // Required for DestroysModelRecords trait
    public static $model = ProcessStatusHistory::class;

    public function index($record)
    {
        $record = Process::withRelationsForHistoryPage()
            ->findOrFail($record)
            ->append(['title']); // Used on page title

        return Inertia::render('departments/MAD/pages/process-status-history/Index', [
            // Refetched after deleting history records
            'history' => $record->statusHistory,

            // Never refetched again. But lazy load isn`t used because 'attachments' depends on 'record'
            'record' => $record,

            // Lazy loads, never refetched again
            'breadcrumbs' => $record->generateBreadcrumbs('MAD'),
        ]);
    }

    public function edit($record)
    {
        $record = Process::withRelationsForHistoryPage()
            ->findOrFail($record)
            ->append(['title']); // Used on page title

        return Inertia::render('departments/MAD/pages/process-status-history/Index', [
            // Refetched after deleting history records
            'history' => $record->statusHistory,

            // Never refetched again. But lazy load isn`t used because 'attachments' depends on 'record'
            'record' => $record,

            // Lazy loads, never refetched again
            'breadcrumbs' => $record->generateBreadcrumbs('MAD'),
        ]);
    }
}
