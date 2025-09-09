<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecipeListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Авторизация проверяется отдельно
    }

    public function prepareForValidation()
    {
        $this->merge([
            'is_published' => filter_var($this->is_published, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'is_published' => 'nullable|boolean',
            'author_id'    => 'nullable|integer|exists:users,id',
            'tag_ids'      => 'nullable|array',
            'tag_ids.*'    => 'integer|exists:tags,id',
            'search'       => 'nullable|string|max:255',
            'per_page'     => 'nullable|integer|min:1|max:100',
        ];
    }

    public function filters(): array
    {
        return $this->only(['is_published', 'author_id', 'tag_ids', 'search', 'page']);
    }

    public function perPage(): int
    {
        return $this->input('per_page', 15);
    }
}
