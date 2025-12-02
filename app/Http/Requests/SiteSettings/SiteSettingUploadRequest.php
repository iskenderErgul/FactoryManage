<?php

namespace App\Http\Requests\SiteSettings;

use Illuminate\Foundation\Http\FormRequest;

class SiteSettingUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'in:site_logo,site_favicon'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,ico', 'max:2048'],
        ];
    }
}


