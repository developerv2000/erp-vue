<?php

namespace App\Http\Controllers\global;

use App\Http\Controllers\Controller;
use App\Support\Contracts\Model\PreparesFetchedRecordsForExport;
use App\Support\Helpers\FileHelper;
use App\Support\Helpers\ModelHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelStorageController extends Controller
{
    /**
     * Generate Excel file for the given model.
     */
    public function generate(Request $request, string $model)
    {
        $modelClass = $this->resolveModelClass($model);

        if (! method_exists($modelClass, 'queryForExport')) {
            abort(400, "Model [$modelClass] must implement queryForExport(Request)");
        }

        $query = $modelClass::queryForExport($request);

        // Load the Excel template
        $template = storage_path($modelClass::STORAGE_PATH_OF_EXCEL_TEMPLATE_FILE_FOR_EXPORT);
        $spreadsheet = IOFactory::load($template);
        $sheet = $spreadsheet->getActiveSheet();

        // Privileged users can export all records, others are limited
        if (Gate::allows('export-unlimited-records-as-excel')) {
            $this->fillSheetByChunkingRecords($modelClass, $query, $sheet);
        } else {
            $this->fillSheetByLimitedRecords($modelClass, $query, $sheet);
        }

        // Save and return the generated filename
        $filename = $this->saveExcelFile($modelClass, $spreadsheet);

        return response()->json([
            'filename' => $filename
        ]);
    }

    /**
     * Download previously generated Excel file.
     */
    public function download(Request $request, string $model, string $filename)
    {
        $modelClass = $this->resolveModelClass($model);
        $filePath = storage_path($modelClass::STORAGE_PATH_OF_EXPORTED_EXCEL_FILES . '/' . $filename);

        if (! file_exists($filePath)) {
            abort(404, 'Exported file not found.');
        }

        return response()->download($filePath)->deleteFileAfterSend(false);
    }

    /**
     * Fill sheet with chunked records (for large exports).
     */
    private function fillSheetByChunkingRecords($modelClass, $query, &$sheet): void
    {
        $row = 2;

        $query->chunk(800, function ($recordsChunk) use (&$sheet, &$row, $modelClass) {
            if (in_array(PreparesFetchedRecordsForExport::class, class_implements($modelClass))) {
                $modelClass::prepareFetchedRecordsForExport($recordsChunk);
            }

            foreach ($recordsChunk as $record) {
                $this->writeRow($sheet, $row++, $record->getExcelColumnValuesForExport());
            }
        });
    }

    /**
     * Fill sheet with limited records (for restricted users).
     */
    private function fillSheetByLimitedRecords($modelClass, $query, &$sheet): void
    {
        $row = 2;

        $records = $query->limit($modelClass::LIMITED_EXCEL_RECORDS_COUNT_FOR_EXPORT)->get();

        if (in_array(PreparesFetchedRecordsForExport::class, class_implements($modelClass))) {
            $modelClass::prepareFetchedRecordsForExport($records);
        }

        foreach ($records as $record) {
            $this->writeRow($sheet, $row++, $record->getExcelColumnValuesForExport());
        }
    }

    /**
     * Save Excel file to storage and return filename.
     */
    private function saveExcelFile($modelClass, $spreadsheet)
    {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = date('Y-m-d H-i-s') . '.xlsx';
        $filename = FileHelper::ensureUniqueFilename($filename, storage_path($modelClass::STORAGE_PATH_OF_EXPORTED_EXCEL_FILES));
        $filePath = storage_path($modelClass::STORAGE_PATH_OF_EXPORTED_EXCEL_FILES . '/' . $filename);

        $writer->save($filePath);

        return $filename;
    }

    /**
     * Write a single row into the sheet.
     */
    private function writeRow(&$sheet, int $row, array $values): void
    {
        $columnIndex = 1;
        foreach ($values as $value) {
            $sheet->setCellValue([$columnIndex++, $row], $value);
        }
    }

    /**
     * Resolve model class from slug.
     */
    private function resolveModelClass(string $model): string
    {
        $modelClass = ModelHelper::addFullNamespaceToModelBasename($model);

        if (! class_exists($modelClass)) {
            abort(404, "Model [$modelClass] not found.");
        }

        return $modelClass;
    }
}
