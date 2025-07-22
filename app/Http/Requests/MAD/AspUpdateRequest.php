<?php

namespace App\Http\Requests\MAD;

use App\Models\MadAsp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AspUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $recordID = $this->route('record')->id;

        return [
            'year' => [Rule::unique(MadAsp::class)->ignore($recordID)]
        ];
    }
}
