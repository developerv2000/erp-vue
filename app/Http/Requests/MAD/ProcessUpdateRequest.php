<?php

namespace App\Http\Requests\MAD;

use App\Models\ProcessStatus;
use Illuminate\Foundation\Http\FormRequest;

class ProcessUpdateRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $status = ProcessStatus::findOrFail($this->input('status_id'));
        $stage = $status->generalStatus->stage;

        // Base fields available for ALL stages
        $baseFields = [
            'product_id',
            'product_form_id',
            'product_dosage',
            'product_pack',
            'product_shelf_life_id',
            'product_class_id',
            'product_moq',
            'status_id',
            'country_ids',
            'responsible_person_id',
            'comment',
        ];

        // Stage-specific fields
        $stageFields = [
            2 => [
                'forecast_year_1',
                'forecast_year_2',
                'forecast_year_3',
                'down_payment_1',
                'down_payment_2',
                'down_payment_condition',
                'dossier_status',
                'clinical_trial_year',
                'clinical_trial_country_ids',
                'clinical_trial_ich_country',
            ],
            3 => [
                'manufacturer_first_offered_price',
                'manufacturer_followed_offered_price',
                'currency_id',
                'our_first_offered_price',
                'our_followed_offered_price',
                'marketing_authorization_holder_id',
                'trademark_en',
                'trademark_ru',
            ],
            4 => [
                'agreed_price',
                'increased_price',
            ],
        ];

        // Merge all allowed fields for the current and previous stages
        $allowedFields = $baseFields;

        foreach ($stageFields as $s => $fields) {
            if ($s <= $stage) {
                $allowedFields = array_merge($allowedFields, $fields);
            }
        }

        // Filter request data
        $this->replace($this->only($allowedFields));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
