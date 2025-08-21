<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'id'             => 'nullable|integer|exists:recipes,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'preview_image'  => 'nullable|image|max:2048', // картинка до 2 МБ
            'servings'       => 'required|integer|min:1', // количество порций
            'cooking_time'   => 'nullable|integer|min:1',
            'is_published'   => 'boolean',

            // Ингредиенты
            'ingredients'                => 'required|array|min:1',
            'ingredients.*.id'           => 'required|exists:ingredients,id',
            'ingredients.*.weight_grams' => 'required|numeric|min:1',

            // Шаги
            'steps'                  => 'required|array|min:1',
            'steps.*.description'    => 'required|string',
            'steps.*.image'          => 'nullable|image|max:2048',
            'steps.*.step_number'     => 'required|integer|min:1',

            // Теги
            'tags'      => 'nullable|array',
            'tags.*'    => 'exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название рецепта обязательно.',
            'ingredients.required' => 'Нужно указать хотя бы один ингредиент.',
            'steps.required' => 'Нужно добавить хотя бы один шаг приготовления.',
        ];
    }
}
