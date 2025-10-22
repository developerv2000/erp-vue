<?php

namespace App\Http\Requests\MAD;

use App\Models\ProcessStatusHistory;
use Illuminate\Foundation\Http\FormRequest;

class ProcessStatusHistoryUpdateRequest extends FormRequest
{
        /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // If record is active, we remove status_id and end_date from validation
        $history = ProcessStatusHistory::findOrFail($this->route('record'));

        $rules = [
            'id' => ['required', 'integer'],
            'start_date' => ['required', 'date_format:Y-m-d H:i:s'],
        ];

        if (! $history->is_active_history) {
            $rules['status_id'] = ['required', 'integer'];
            $rules['end_date'] = ['required', 'date_format:Y-m-d H:i:s'];
        }

        return $rules;
    }
}
