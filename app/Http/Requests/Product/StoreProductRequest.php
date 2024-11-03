<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'production_cost' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'product_name.required' => 'Ürün adı gereklidir.',
            'product_name.string' => 'Ürün adı bir metin olmalıdır.',
            'product_name.max' => 'Ürün adı en fazla 255 karakter olabilir.',
            'product_type.required' => 'Ürün tipi gereklidir.',
            'product_type.string' => 'Ürün tipi bir metin olmalıdır.',
            'product_type.max' => 'Ürün tipi en fazla 255 karakter olabilir.',
            'production_cost.required' => 'Üretim maliyeti gereklidir.',
            'production_cost.numeric' => 'Üretim maliyeti bir sayı olmalıdır.',
            'production_cost.min' => 'Üretim maliyeti 0 veya daha büyük olmalıdır.',
            'stock_quantity.required' => 'Stok miktarı gereklidir.',
            'stock_quantity.integer' => 'Stok miktarı bir tamsayı olmalıdır.',
            'stock_quantity.min' => 'Stok miktarı 0 veya daha büyük olmalıdır.',
            'description.string' => 'Açıklama bir metin olmalıdır.',
            'description.max' => 'Açıklama en fazla 1000 karakter olabilir.',
        ];
    }
}
