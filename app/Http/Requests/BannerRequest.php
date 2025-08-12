<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest {
    public function rules()
    {
        return [
            'code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
