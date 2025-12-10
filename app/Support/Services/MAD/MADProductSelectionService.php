<?php

namespace App\Support\Services\MAD;

use App\Models\ProductSearchStatus;
use App\Support\Helpers\FileHelper;
use App\Support\Helpers\ModelHelper;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MADProductSelectionService
{
    // Storage
    const EXCEL_TEMPLATE_FILE_PATH = 'app/private/excel/export-templates/product-selection.xlsx';
    const GENERATED_EXCEL_FILES_PATH = 'app/private/excel/exports/product-selection';

    // Default countries to include
    const DEFAULT_COUNTRIES = [
        'KZ',
        'TM',
        'KG',
        'AM',
        'TJ',
        'UZ',
        'GE',
        'MN',
        'RU',
        'AZ',
        'AL',
        'KE',
        'DO',
        'KH',
        'MM',
    ];

    // Excel constants
    const TITLES_ROW = 1;
    const SUBTITLES_ROW = 2;
    const START_INSERTING_RECORDS_FROM_ROW = 4;

    const FIRST_DEFAULT_COUNTRY_COLUMN_SUBTITLE_LETTER = 'L';
    const LAST_DEFAULT_COUNTRY_COLUMN_SUBTITLE_LETTER = 'Z';
    const COLUMNS_COUNT_FROM_LAST_COUNTRY_TO_FIRST_FORECAST = 7;
    const LAST_DEFAULT_FORECAST_COLUMN_SUBTITLE_LETTER = 'BY';

    protected string $model;
    protected string $modelClass;
    protected string $storagePath;

    public function __construct(string $model)
    {
        $this->model = $model;
        $this->modelClass = $this->resolveModelClass($model);
        $this->storagePath = $this->resolveStoragePath($model);
    }

    public function generateExcel()
    {
        $spreadsheet = $this->buildSpreadsheet();

        return $this->saveSpreadsheet($spreadsheet);
    }

    public function downloadExcel(string $filename)
    {
        $file = $this->storagePath . '/' . $filename;

        if (! file_exists($file)) {
            abort(404);
        }

        return response()->download($file);
    }

    /*
    |--------------------------------------------------------------------------
    | General helpers
    |--------------------------------------------------------------------------
    */

    protected function resolveModelClass(string $model): string
    {
        $allowed = [
            'Product',
            'Process',
        ];

        if (!in_array($model, $allowed)) {
            abort(404);
        }

        $class = ModelHelper::addFullNamespaceToModelBasename($model);

        if (!class_exists($class)) {
            abort(404, "Model [$class] does not exist.");
        }

        return $class;
    }

    protected function resolveStoragePath(string $model): string
    {
        return storage_path(self::GENERATED_EXCEL_FILES_PATH . '/' . $model);
    }

    protected function buildSpreadsheet()
    {
        $sheet = $this->getActiveSheetFromTemplate();

        // Collect all records with required appends
        $records = $this->collectRecords();

        // Insert 'additional country subtitles' into sheet
        $countries = $this->resolveCountries($records, $sheet);

        // Insert 'additional country forecast titles and subtitles' into sheet
        $this->insertAdditionalForecastTitlesIntoSheet($sheet, $countries['additional']);

        // Insert records into sheet
        $this->insertRecordsIntoSheet($sheet, $records, $countries);

        // Return sheet
        return $sheet;
    }

    protected function getActiveSheetFromTemplate()
    {
        $templatePath = storage_path(self::EXCEL_TEMPLATE_FILE_PATH);
        $spreadsheet = IOFactory::load($templatePath);

        return $spreadsheet->getActiveSheet();
    }

    protected function collectRecords()
    {
        $query = $this->modelClass::queryRecordsForProductSelection();

        // Collect all records by chunks
        $records = collect();

        $query->chunk(1200, function ($chunked) use (&$records) {
            $records = $records->merge($chunked);
        });

        // Append 'matched_product_searches' only for 'Product' model
        if ($this->model == 'Product') {
            $this->appendActiveProductSearches($records);
        }
    }

    /**
     * Insert into sheet 'additional country subtitles'
     * after 'default country subtitles' and return countries array.
     */
    protected function resolveCountries($records, $sheet)
    {
        // Get countries
        $countries = [
            'default' => self::DEFAULT_COUNTRIES,
            'additional' => $this->getAdditionalCountries($records),
        ];

        $countries['all'] = array_merge($countries['default'], $countries['additional']);

        // Insert additional country subtitles
        $this->insertAdditionalCountrySubtitlesIntoSheet($sheet, $countries['additional']);

        // Return countries
        return $countries;
    }

    protected function getAdditionalCountries($records)
    {
        // 1. Collect unique additional countries from records

        // For 'Product' model we need to collect unique countries of 'active_product_searches'
        if ($this->model == 'Product') {
            $uniqueCountries = $records->flatMap->active_product_searches->pluck('country.code')->unique();
        }

        // For 'Process' model we need to collect unique countries from 'searchCountry' relation
        if ($this->model == 'Process') {
            $uniqueCountries = $records->pluck('searchCountry.code')->unique();
        }

        // 2. Remove countries which already present in default countries
        $additionalCountries = $uniqueCountries->diff(self::DEFAULT_COUNTRIES);

        // 3. Return additional countries
        return $additionalCountries;
    }

    /**
     * Requires refactoring!
     */
    protected function insertAdditionalCountrySubtitlesIntoSheet(Worksheet $sheet, $additionalCountries)
    {
        // Exit if no additional countries
        if (count($additionalCountries) == 0) {
            return;
        }

        // insert 'additional country subtitles' after 'last default country subtitle'
        $startColIndex = Coordinate::columnIndexFromString(self::LAST_DEFAULT_COUNTRY_COLUMN_SUBTITLE_LETTER) + 1;

        foreach ($additionalCountries as $country) {
            // Insert new country column
            $nextColIndex = $startColIndex + 1;
            $nextColLetter = Coordinate::stringFromColumnIndex($nextColIndex);
            $sheet->insertNewColumnBefore($nextColLetter);

            $insertedCellCoordinates = [$nextColLetter, self::SUBTITLES_ROW];
            $sheet->setCellValue($insertedCellCoordinates, $country);

            // Update cell styles
            $sheet->getColumnDimension($nextColLetter)->setWidth(5);
            $cellStyle = $sheet->getCell($insertedCellCoordinates)->getStyle();
            $this->highlightAdditionalInsertedCell($cellStyle);

            // Update startColIndex
            $startColIndex = $nextColIndex; // increment
        }
    }

    protected function highlightAdditionalInsertedCell($cellStyle)
    {
        $cellStyle->getFill()->getStartColor()->setARGB('00FFFF');
        $cellStyle->getFont()->setColor(new Color(Color::COLOR_BLACK));
    }

    protected function insertAdditionalForecastTitlesIntoSheet($sheet, $additionalCountries)
    {
        // Starting column index after default countries
        $startColIndex = $this->detectLastForecastNumericIndexOfDefaultCountries($additionalCountries) + 1;

        foreach ($additionalCountries as $country) {
            // 1. Set forecast title

            // 3-column group for one country forecast title
            $firstColIndex = $startColIndex;
            $lastColIndex  = $startColIndex + 2;

            // Convert numeric index to Excel letters
            $firstLetter = Coordinate::stringFromColumnIndex($firstColIndex);
            $lastLetter  = Coordinate::stringFromColumnIndex($lastColIndex);

            // Merge header cells
            $mergeRange = $firstLetter . self::TITLES_ROW . ':' . $lastLetter . self::TITLES_ROW;
            $sheet->mergeCells($mergeRange);

            // Set country title
            $sheet->setCellValue([$firstColIndex, self::TITLES_ROW], "Forecast " . $country);

            // 2. Set forecast subtitles
            $sheet->setCellValue([$firstColIndex + 1, self::SUBTITLES_ROW], "YEAR 1");
            $sheet->setCellValue([$firstColIndex + 2, self::SUBTITLES_ROW], "YEAR 2");
            $sheet->setCellValue([$firstColIndex + 3, self::SUBTITLES_ROW], "YEAR 3");

            // Move to next 3-column block
            $startColIndex += 3;
        }
    }

    protected function detectLastForecastNumericIndexOfDefaultCountries($additionalCountries)
    {
        // Exit if no additional countries
        if (count($additionalCountries) == 0) {
            return;
        }

        // Index before inserting additional countries
        $numericIndexBefore = Coordinate::columnIndexFromString(self::LAST_DEFAULT_FORECAST_COLUMN_SUBTITLE_LETTER);

        // Index after inserting additional countries
        $numericIndexAfter = $numericIndexBefore + count($additionalCountries);

        return $numericIndexAfter;
    }

    protected function insertRecordsIntoSheet($sheet, $records, $countries)
    {
        match ($this->model) {
            'Product' => $this->insertProductRecordsIntoSheet($sheet, $records, $countries),
            'Process' => $this->insertProcessRecordsIntoSheet($sheet, $records, $countries),
        };
    }

    protected function saveSpreadsheet($spreadsheet)
    {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $filename = FileHelper::ensureUniqueFilename(
            now()->format('Y-m-d H-i-s') . '.xlsx',
            $this->storagePath
        );

        $writer->save("{$this->storagePath}/{$filename}");

        return $filename;
    }

    /*
    |--------------------------------------------------------------------------
    | Product model helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Append 'active_product_searches' to each records.
     *
     * Append only active searches, skipping "canceled" ones.
     */
    protected function appendActiveProductSearches($records)
    {
        $canceledStatusID = ProductSearchStatus::getCanceledStatusID();

        $records->each(function ($record) use ($canceledStatusID) {
            $matches = $record->matched_product_searches;
            $onlyActive = $matches->filter(fn($record) => $record->status_id != $canceledStatusID);
            $record->active_product_searches = $onlyActive;
        });
    }

    protected function insertProductRecordsIntoSheet($sheet, $records, $countries) {}

    /*
    |--------------------------------------------------------------------------
    | Process model helpers
    |--------------------------------------------------------------------------
    */

    protected function insertProcessRecordsIntoSheet($sheet, $records, $countries) {}
}
