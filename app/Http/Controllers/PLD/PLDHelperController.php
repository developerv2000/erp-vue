<?php

namespace App\Http\Controllers\PLD;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use App\Models\Process;
use Illuminate\Http\Request;

class PLDHelperController extends Controller
{
    /**
     * AJAX request
     *
     * Used on 'pld.orders.create' page to fetch 'ready for order processes'
     * of the selected manufacturer and country.
     */
    public function getReadyForOrderProcessesOfManufacturer(Request $request)
    {
        $manufacturer = Manufacturer::findOrFail($request->input('manufacturer_id'));
        $countryId = $request->input('country_id');

        return $manufacturer->getReadyForOrderProcessesOfCountry($countryId, appendFullEnglishProductLabelWithId: true);
    }

    /**
     * AJAX request
     *
     * Used on 'pld.orders.create' page to fetch 'ready for order processes'
     * of the selected manufacturer and country and Trademark EN.
     */
    public function getProcessWithItSimilarRecordsForOrder(Request $request)
    {
        $process = Process::findOrFail($request->input('process_id'));

        return $process->getSelfWithSimilarRecordsForOrder(appendMAHNameWithID: true);
    }
}
