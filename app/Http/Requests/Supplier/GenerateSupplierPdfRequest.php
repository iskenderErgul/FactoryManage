<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class GenerateSupplierPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'all_history' => 'boolean',
            'start_date' => 'nullable|required_if:all_history,false|date|before_or_equal:end_date',
            'end_date' => 'nullable|required_if:all_history,false|date|after_or_equal:start_date',
            'display' => 'nullable|string|in:inline,download',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required_if' => 'Tarih aralığı seçiniz veya Tüm Geçmiş seçeneğini işaretleyiniz.',
            'end_date.required_if' => 'Bitiş tarihi zorunludur.',
            'start_date.before_or_equal' => 'Başlangıç tarihi bitiş tarihinden büyük olamaz.',
        ];
    }
}
