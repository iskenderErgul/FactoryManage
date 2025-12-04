<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class ProductionReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'sometimes|date|date_format:Y-m-d',
            'end_date' => 'sometimes|date|date_format:Y-m-d|after_or_equal:start_date',
            'product_id' => 'sometimes|integer|exists:products,id',
            'user_id' => 'sometimes|integer|exists:users,id',
            'machine_id' => 'sometimes|integer|exists:machines,id',
            'year' => 'sometimes|integer|min:2020|max:2100',
            'month' => 'sometimes|integer|min:1|max:12',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date_format' => 'Başlangıç tarihi YYYY-MM-DD formatında olmalıdır.',
            'end_date.date_format' => 'Bitiş tarihi YYYY-MM-DD formatında olmalıdır.',
            'end_date.after_or_equal' => 'Bitiş tarihi başlangıç tarihinden önce olamaz.',
            'product_id.exists' => 'Geçersiz ürün seçimi.',
            'user_id.exists' => 'Geçersiz kullanıcı seçimi.',
            'machine_id.exists' => 'Geçersiz makine seçimi.',
        ];
    }
}
