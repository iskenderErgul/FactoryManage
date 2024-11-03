<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Bu isteğin kim tarafından yapılabileceğine dair bir kısıtlama yok
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
