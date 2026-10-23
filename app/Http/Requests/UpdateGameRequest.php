<?php

namespace App\Http\Requests;

use App\Enums\GameStatus;
use App\Models\Game;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::authorize('update', $this->route('game'))->allowed();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Game::class)->where('user_id', $this->user()->id)->ignore($this->route('game')),
            ],
            'status' => ['nullable', Rule::enum(GameStatus::class)->except(GameStatus::Completed)],
            'rawg_id' => ['nullable', 'integer', 'min:1'],
            'cover_url' => ['nullable', 'url:https', 'max:2048'],
        ];
    }
}
