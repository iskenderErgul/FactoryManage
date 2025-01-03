<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'sale_date' => 'required|date_format:Y-m-d',
            'products' => 'required|array',
        ];
    }
}
