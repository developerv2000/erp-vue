<?php

namespace App\Http\Requests\MAD;

use App\Models\Process;
use App\Models\ProcessStatus;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'country_id',
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
        $record = Process::withTrashed()
            ->findOrFail($this->route('record'));

        return [
            'country_id' => [
                Rule::unique(Process::class)
                    ->ignore($record->id)
                    ->where('product_id', $record->product_id)
                    // ->where('country_id', $this->country_id) // already included
                    ->where('marketing_authorization_holder_id', $this->marketing_authorization_holder_id)
            ]
        ];
    }

    /**
     * Validate uniqueness of related product.
     *
     * IMPORTANT: Must be synced with ProductUpdateRequest!
     */
    public function validateProductUniqueness($product)
    {
        $this->validate(
            [
                'product_form_id' => [
                    Rule::unique('products', 'form_id')
                        ->ignore($product->id)
                        // ->where('form_id', $this->product_form_id) // already included
                        ->where('manufacturer_id', $product->manufacturer_id)
                        ->where('inn_id', $product->inn_id)
                        ->where('dosage', $this->product_dosage)
                        ->where('pack', $this->product_pack)
                        ->where('moq', $this->product_moq)
                        ->where('shelf_life_id', $this->product_shelf_life_id)
                ],
            ],

            [
                'product_form_id.unique' => trans('validation.custom.ivp.unique_on_edit'),
            ]
        );
    }

    public function messages(): array
    {
        return [
            'country_id.unique' => trans('validation.custom.vps.unique_on_edit'),
        ];
    }
}
