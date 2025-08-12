<?php

namespace App\Http\Requests;

use App\Enums\LogLevels;
use App\Facades\ClickHouseLog;
use Illuminate\Foundation\Http\FormRequest;

class BannerUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => $this->input('banner.title'),
            'code' => $this->input('banner.code'),
            'short_description' => $this->input('banner.short_description'),
            'active' => !empty($this->input('banner.active')),
            'image' => $this->input('banner.image'),
        ]);
    }

    public function rules()
    {
        return [
            'short_description' => ['nullable', 'string', 'max:500'],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            // Всегда массив
            'image' => ['nullable', 'array'],
            'image.*' => ['integer', 'exists:attachments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.*.exists' => 'Передан несуществующий файл',
        ];
    }
}
