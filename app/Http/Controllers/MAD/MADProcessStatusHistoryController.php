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

    public function index($process)
    {
        $process = Process::withRelationsForHistoryPage()
            ->findOrFail($process)
            ->append(['title']); // Used on page title

        return Inertia::render('departments/MAD/pages/process-status-history/Index', [
            // Refetched after deleting history records of process
            'historyRecords' => $process->statusHistory,

            // Lazy loads, never refetched again
            'process' => fn() => $process, // 'historyRecords' depends on 'process'
            'breadcrumbs' => fn() => $process->generateBreadcrumbs('MAD'),
        ]);
    }

    public function edit($record)
    {

    }
}
