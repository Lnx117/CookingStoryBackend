<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BannerUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'title' => $this->input('banner.title'),
            'url' => $this->input('banner.url'),
            'short_description' => $this->input('banner.short_description'),
            'active' => $this->input('banner.active'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'short_description' => ['nullable', 'string', 'max:500'],
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.url' => 'Поле URL должно содержать допустимый адрес веб-страницы. Например https://ru.wikipedia.org/wiki/',
        ];
    }
}
