<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class SalesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'sale_date' => 'required|',
            'products' => 'required|array',

        ];
    }


}
