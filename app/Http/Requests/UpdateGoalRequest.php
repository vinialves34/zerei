<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGoalRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['year' => $this->route('year')]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'between:2000,'.(now()->year + 1)],
            'target' => ['required', 'integer', 'between:1,999'],
        ];
    }
}
