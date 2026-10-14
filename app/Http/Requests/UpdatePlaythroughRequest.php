<?php

namespace App\Http\Requests;

use App\Enums\CompletionLevel;
use App\Enums\Platform;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdatePlaythroughRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::authorize('update', $this->route('playthrough'))->allowed();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'completed_at' => ['required', 'date', 'before_or_equal:today'],
            'platform' => ['required', Rule::enum(Platform::class)],
            'rating' => ['required', 'integer', 'between:1,5'],
            'completion_level' => ['required', Rule::enum(CompletionLevel::class)],
        ];
    }
}
