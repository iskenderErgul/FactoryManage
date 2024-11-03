<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
            'id' => 'required|integer|exists:customers,id',  // ID zorunlu, integer ve customers tablosunda mevcut olmalı
            'name' => 'required|string|max:255',  // İsim zorunlu, en fazla 255 karakter olmalı
            'email' => 'required|email|max:255|unique:customers,email,' . $this->id,  // E-posta zorunlu, geçerli bir formatta olmalı, en fazla 255 karakter ve unique kontrolü (bu id'ye ait olmasın)
            'phone' => 'required|string',  // Telefon zorunlu, en fazla 15 karakter olmalı (ülke kodu ile birlikte)
            'address' => 'required|string|max:255',  // Adres zorunlu, en fazla 255 karakter olmalı
            'created_at' => 'nullable|date',  // Oluşturulma tarihi isteğe bağlı, tarih formatında olmalı
            'updated_at' => 'nullable|date',  // Güncellenme tarihi isteğe bağlı, tarih formatında olmalı
        ];
    }
}
