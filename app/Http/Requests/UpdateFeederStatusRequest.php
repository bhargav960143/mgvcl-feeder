<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeederStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateStatus', $this->route('feeder'));
    }

    public function rules(): array
    {
        return [
            'status'  => ['required', Rule::in(['fully_on', 'partially_on', 'fully_off'])],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please select a status.',
            'status.in'       => 'Invalid status selected.',
        ];
    }
}
