<?php

namespace App\Http\Requests\MAD;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Rules are handled dynamically per record
        return [];
    }

    /**
     * Validate uniqueness of record based on business attributes.
     *
     * IMPORTANT: Must be synced with ProductUpdateRequest,
     * ProcessCreateRequest and ProcessUpdateRequest
     */
    public function validateUniquenessOfRecord()
    {
        $this->validate(
            [
                'inn_id' =>
                [
                    Rule::unique(Product::class)->where(function ($query) {
                        $query->where('manufacturer_id', $this->manufacturer_id)
                            // ->where('inn_id', $this->inn_id) // already included
                            ->where('form_id', $this->form_id)
                            ->where('dosage', $this->dosage)
                            ->where('pack', $this->pack)
                            ->where('moq', $this->moq)
                            ->where('shelf_life_id', $this->shelf_life_id);
                    }),
                ],
            ],

            [
                'inn_id.unique' => trans('validation.custom.ivp.unique_on_create', [
                    'dosage' => $this->dosage,
                    'pack' => $this->pack,
                    'moq' => $this->moq,
                ]),
            ]
        );
    }
}
