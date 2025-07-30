<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class StoreByWorkerProductionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'machine_id' => 'required|integer|exists:machines,id',
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Kullanıcı ID gereklidir.',
            'user_id.exists' => 'Geçersiz kullanıcı.',
            'machine_id.required' => 'Makine seçimi gereklidir.',
            'machine_id.exists' => 'Geçersiz makine.',
            'product_id.required' => 'Ürün seçimi gereklidir.',
            'product_id.exists' => 'Geçersiz ürün.',
            'quantity.required' => 'Miktar gereklidir.',
            'quantity.numeric' => 'Miktar sayısal olmalıdır.',
            'quantity.min' => 'Miktar 0\'dan büyük olmalıdır.',
        ];
    }
}
