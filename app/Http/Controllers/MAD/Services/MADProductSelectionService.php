<?php

namespace App\Http\Controllers\MAD\Services;

use App\Models\ProductSearchStatus;
use App\Support\Helpers\FileHelper;
use App\Support\Helpers\ModelHelper;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    // Start inserting new rows from 5 (not 4), to copy row 4 (not 3) styles when inserting new rows
    const START_INSERTING_RECORDS_FROM_ROW = 5;

    const FIRST_DEFAULT_COUNTRY_COLUMN_SUBTITLE_LETTER = 'L';
    const LAST_DEFAULT_COUNTRY_COLUMN_SUBTITLE_LETTER = 'Z';
    const COLUMNS_COUNT_AFTER_LAST_COUNTRY_TO_FIRST_FORECAST = 6;
    const LAST_DEFAULT_FORECAST_COLUMN_SUBTITLE_LETTER = 'BY';

    protected string $model;
    protected string $modelClass;
    protected string $storagePath;

    protected array $matchedCellStyle = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '92D050'],
        ],
    ];

    protected array $additionalInsertedCellStyle = [
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '00FFFF'],
        ],
        'font' => [
            'bold' => true,
            'color' => ['rgb' => '000000'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    public function __construct(string $model)
    {
        $this->model = $model;
        $this->modelClass = $this->resolveModelClass($model);
        $this->storagePath = $this->resolveStoragePath($model);
    }

    public function generateExcel(): string
    {
        $spreadsheet = $this->buildSpreadsheet();

        return $this->saveSpreadsheet($spreadsheet);
    }

    public function downloadExcel(string $filename): BinaryFileResponse
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

    protected function buildSpreadsheet(): Spreadsheet
    {
        $templatePath = storage_path(self::EXCEL_TEMPLATE_FILE_PATH);
        $spreadsheet = IOFactory::load($templatePath);
        $activeSheet = $spreadsheet->getActiveSheet();

        // Collect all records with required appends
        $records = $this->collectRecords();

        // Insert 'additional country subtitles' into sheet
        $countries = $this->resolveCountries($records, $activeSheet);

        // Insert 'additional country forecast titles and subtitles' into sheet
        $this->insertAdditionalForecastTitlesIntoSheet($activeSheet, $countries['additional']);

        // Insert records into sheet
        $this->insertRecordsIntoSheet($activeSheet, $records, $countries);

        // Return sheet
        return $spreadsheet;
    }

    protected function collectRecords(): Collection
    {
        $query = $this->modelClass::queryRecordsForProductSelection(request());

        // Collect all records by chunks
        $records = collect();

        $query->chunk(1200, function ($chunked) use (&$records) {
            $records = $records->merge($chunked);
        });

        // Append 'matched_product_searches' only for 'Product' model
        if ($this->model == 'Product') {
            $this->appendActiveProductSearches($records);
        }

        return $records;
    }

    /**
     * Insert into sheet 'additional country subtitles'
     * after 'default country subtitles' and return countries array.
     */
    protected function resolveCountries($records, $sheet): array
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

    protected function getAdditionalCountries($records): array
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
        return $additionalCountries->toArray();
    }

    /**
     * Requires refactoring!
     */
    protected function insertAdditionalCountrySubtitlesIntoSheet($sheet, $additionalCountries): void
    {
        // Exit if no additional countries
        if (count($additionalCountries) == 0) {
            return;
        }

        // Highlight new countries
        $highlightCells = [];

        // insert 'additional country subtitles' after 'last default country subtitle'
        $startColIndex = Coordinate::columnIndexFromString(self::LAST_DEFAULT_COUNTRY_COLUMN_SUBTITLE_LETTER);

        foreach ($additionalCountries as $country) {
            // Insert new country column
            $nextColIndex = $startColIndex + 1;
            $nextColLetter = Coordinate::stringFromColumnIndex($nextColIndex);
            $sheet->insertNewColumnBefore($nextColLetter);

            $insertedCellCoordinates = [$nextColIndex, self::SUBTITLES_ROW];
            $sheet->setCellValue($insertedCellCoordinates, $country);

            // Update cell styles
            $sheet->getColumnDimension($nextColLetter)->setWidth(5);
            $highlightCells[] = $insertedCellCoordinates;

            // Update startColIndex
            $startColIndex = $nextColIndex; // increment
        }

        // Highlight new countries
        $this->applyBatchStyles($sheet, $highlightCells, $this->additionalInsertedCellStyle);
    }

    protected function applyBatchStyles($sheet, array $cells, array $styleArray): void
    {
        foreach ($cells as $cell) {
            // $cell = [columnIndex, rowNumber]
            $columnLetter = Coordinate::stringFromColumnIndex($cell[0]);
            $cellCoordinate = $columnLetter . $cell[1];

            $sheet->getStyle($cellCoordinate)->applyFromArray($styleArray);
        }
    }

    protected function insertAdditionalForecastTitlesIntoSheet($sheet, $additionalCountries): void
    {
        // Highlight new country forecasts
        $highlightCells = [];

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
            $sheet->setCellValue([$firstColIndex, self::TITLES_ROW], "FORECAST " . $country);

            // Highlight inserted title
            $highlightCells[] = [$firstColIndex, self::TITLES_ROW];

            // 2. Set forecast subtitles
            $sheet->setCellValue([$firstColIndex, self::SUBTITLES_ROW], "YEAR 1");
            $sheet->setCellValue([$firstColIndex + 1, self::SUBTITLES_ROW], "YEAR 2");
            $sheet->setCellValue([$firstColIndex + 2, self::SUBTITLES_ROW], "YEAR 3");

            // Highlight inserted subtitles
            $highlightCells[] = [$firstColIndex, self::SUBTITLES_ROW];
            $highlightCells[] = [$firstColIndex + 1, self::SUBTITLES_ROW];
            $highlightCells[] = [$firstColIndex + 2, self::SUBTITLES_ROW];

            // Move to next 3-column block
            $startColIndex += 3;
        }

        // Highlight new country forecasts
        $this->applyBatchStyles($sheet, $highlightCells, $this->additionalInsertedCellStyle);
    }

    protected function detectLastForecastNumericIndexOfDefaultCountries($additionalCountries): ?int
    {
        // Exit if no additional countries
        if (count($additionalCountries) == 0) {
            return null;
        }

        // Index before inserting additional countries
        $numericIndexBefore = Coordinate::columnIndexFromString(self::LAST_DEFAULT_FORECAST_COLUMN_SUBTITLE_LETTER);

        // Index after inserting additional countries
        $numericIndexAfter = $numericIndexBefore + count($additionalCountries);

        return $numericIndexAfter;
    }

    protected function insertRecordsIntoSheet($sheet, $records, $countries): void
    {
        match ($this->model) {
            'Product' => $this->insertProductRecordsIntoSheet($sheet, $records, $countries),
            'Process' => $this->insertProcessRecordsIntoSheet($sheet, $records, $countries),
        };
    }

    protected function saveSpreadsheet($spreadsheet): string
    {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $filename = FileHelper::ensureUniqueFilename(
            now()->format('Y-m-d H-i-s') . '.xlsx',
            $this->storagePath
        );

        $writer->save("{$this->storagePath}/{$filename}");

        return $filename;
    }

    /**
     * Remove row 4 instead of row 5 because new records are inserted from row 5.
     */
    protected function removeRedundantRow($sheet, $records): void
    {
        if ($records->count()) {
            $sheet->removeRow(self::START_INSERTING_RECORDS_FROM_ROW - 1);
        }
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
    protected function appendActiveProductSearches($records): void
    {
        $canceledStatusID = ProductSearchStatus::getCanceledStatusID();

        $records->each(function ($record) use ($canceledStatusID) {
            $matches = $record->matched_product_searches;
            $onlyActive = $matches->filter(fn($record) => $record->status_id != $canceledStatusID);
            $record->active_product_searches = $onlyActive;
        });
    }

    protected function insertProductRecordsIntoSheet($sheet, $records, $countries): void
    {
        $row = self::START_INSERTING_RECORDS_FROM_ROW;
        $recordsCounter = 1;
        $firstCountryColumnIndex = Coordinate::columnIndexFromString(self::FIRST_DEFAULT_COUNTRY_COLUMN_SUBTITLE_LETTER);
        $highlightMatchedCells = [];

        // Pre-insert all needed rows at once
        $sheet->insertNewRowBefore($row, $records->count());

        foreach ($records as $record) {
            // Begin from 'A' column
            $columnIndex = 1;

            // 1. Insert record counter
            $sheet->setCellValue([$columnIndex++, $row], $recordsCounter);

            // 2. Insert Inn, form, dosage, pack, MOQ and shelf life
            $sheet->setCellValue([$columnIndex++, $row], $record->inn->name);
            $sheet->setCellValue([$columnIndex++, $row], $record->form->name);
            $sheet->setCellValue([$columnIndex++, $row], $record->dosage);
            $sheet->setCellValue([$columnIndex++, $row], $record->pack);
            $sheet->setCellValue([$columnIndex++, $row], $record->moq);
            $sheet->setCellValue([$columnIndex++, $row], $record->shelfLife->name);

            // 3. Insert active product matches for countries
            $loopColumnIndex = $firstCountryColumnIndex; // Reset for each records and increment after each country loop

            // Loop through all countries
            foreach ($countries['all'] as $country) {
                $cellIndex = [$loopColumnIndex, $row];

                if ($record->active_product_searches->contains('country.code', $country)) {
                    $sheet->setCellValue($cellIndex, 1);

                    // Collect for batch styling later
                    $highlightMatchedCells[] = $cellIndex;
                }

                $loopColumnIndex++; // Move to the next country column
            }

            // Move to the next row
            $row++;
            $recordsCounter++;
        }

        // Highlight matched cells
        $this->applyBatchStyles($sheet, $highlightMatchedCells, $this->matchedCellStyle);

        // Remove redundant row
        $this->removeRedundantRow($sheet, $records);
    }

    /*
    |--------------------------------------------------------------------------
    | Process model helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Optimized, can be more refactored and optimized!
     */
    protected function insertProcessRecordsIntoSheet($sheet, $records, $countries): void
    {
        $row = self::START_INSERTING_RECORDS_FROM_ROW;
        $recordsCounter = 1;

        // Used in getFirstForecastColumnIndexForCountry()
        $allCountriesCount = count($countries['all']);

        // Used inside countries loop
        $firstCountryColumnIndex = Coordinate::columnIndexFromString(self::FIRST_DEFAULT_COUNTRY_COLUMN_SUBTITLE_LETTER);

        // Highlight matched cells
        $highlightMatchedCells = [];

        // IMPORTANT: Create indexed version of records for faster access
        $indexedRecords = $records->keyBy(function ($item) {
            return $item->product_id . '_' . data_get($item, 'searchCountry.code');
        });

        // Loop only through unique records by 'product_id'
        $uniqueRecords = $records->unique('product_id');

        // Pre-insert all needed rows at once
        $sheet->insertNewRowBefore($row, $uniqueRecords->count());

        foreach ($uniqueRecords as $record) {
            // Begin from 'A' column
            $columnIndex = 1;

            // 1. Insert record counter
            $sheet->setCellValue([$columnIndex++, $row], $recordsCounter);

            // 2. Insert Inn, form, dosage, pack, MOQ, shelf life, 'manufacturer_first_offered_price',
            // 'our_first_offered_price', 'agreed_price' and currency
            $sheet->setCellValue([$columnIndex++, $row], $record->product->inn->name);
            $sheet->setCellValue([$columnIndex++, $row], $record->product->form->name);
            $sheet->setCellValue([$columnIndex++, $row], $record->product->dosage);
            $sheet->setCellValue([$columnIndex++, $row], $record->product->pack);
            $sheet->setCellValue([$columnIndex++, $row], $record->product->moq);
            $sheet->setCellValue([$columnIndex++, $row], $record->product->shelfLife->name);
            $sheet->setCellValue([$columnIndex++, $row], $record->manufacturer_first_offered_price);
            $sheet->setCellValue([$columnIndex++, $row], $record->our_first_offered_price);
            $sheet->setCellValue([$columnIndex++, $row], $record->agreed_price);
            $sheet->setCellValue([$columnIndex++, $row], $record->currency?->name);

            // 3. Insert searchCountry matches

            // Values reset for each records row
            $loopColumnIndex = $firstCountryColumnIndex; // Increment after each country loop, used only inside countries loop
            $countriesLoopIndex = 0; // Used on calculating forecast column indexes

            // Loop through all countries
            foreach ($countries['all'] as $country) {
                $countriesLoopIndex++;
                $columnIndex = $loopColumnIndex;
                $cellIndex = [$columnIndex, $row];

                // Search for matches
                $matched = $indexedRecords[$record->product_id . '_' . $country] ?? null;

                if ($matched) {
                    // 4. Insert status if matched
                    $sheet->setCellValue($cellIndex, $matched->status->name);

                    // 5. Insert year 1, 2, 3 forecasts
                    // Detect forecast column start index
                    $firstForecastColumnIndex = $this->getFirstForecastColumnIndexForCountry($countriesLoopIndex, $allCountriesCount);

                    $sheet->setCellValue([$firstForecastColumnIndex, $row], $matched->forecast_year_1);
                    $sheet->setCellValue([$firstForecastColumnIndex + 1, $row], $matched->forecast_year_2);
                    $sheet->setCellValue([$firstForecastColumnIndex + 2, $row], $matched->forecast_year_3);

                    // Collect for batch styling later
                    $highlightMatchedCells[] = $cellIndex;
                }

                $loopColumnIndex++; // Move to the next country column
            }

            // Move to the next row
            $row++;
            $recordsCounter++;
        }

        // Highlight matched cells
        $this->applyBatchStyles($sheet, $highlightMatchedCells, $this->matchedCellStyle);

        // Remove redundant row
        $this->removeRedundantRow($sheet, $records);
    }

    protected function getFirstForecastColumnIndexForCountry($countryIndex, $allCountriesCount): int
    {
        return Coordinate::columnIndexFromString(self::FIRST_DEFAULT_COUNTRY_COLUMN_SUBTITLE_LETTER)
            + $allCountriesCount
            + self::COLUMNS_COUNT_AFTER_LAST_COUNTRY_TO_FIRST_FORECAST
            + (($countryIndex - 1) * 3); // Each country has 3 forecast columns
    }
}
