<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Kullanıcının isteği yapma yetkisi
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:productions,id', // Üretim kaydının ID'si zorunlu ve mevcut olmalı
            'product_id' => 'required|exists:products,id', // Ürün ID'si zorunlu ve mevcut olmalı
            'quantity' => 'required|integer|min:1', // Miktar zorunlu ve en az 1 olmalı
            'machine_id' => 'required|exists:machines,id', // Makine ID'si zorunlu ve mevcut olmalı
            'shift_id' => 'required|exists:shifts,id', // Vardiya ID'si zorunlu ve mevcut olmalı
            'production_date' => 'required|date', // Üretim tarihi zorunlu ve geçerli bir tarih olmalı
            'worker_id' => 'required|exists:users,id', // İşçi ID'si zorunlu ve mevcut olmalı
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'Üretim kaydının ID\'si zorunludur.',
            'id.exists' => 'Belirtilen üretim kaydı mevcut değildir.',
            'product_id.required' => 'Ürün ID\'si zorunludur.',
            'product_id.exists' => 'Seçtiğiniz ürün mevcut değildir.',
            'quantity.required' => 'Miktar zorunludur.',
            'quantity.integer' => 'Miktar bir tam sayı olmalıdır.',
            'quantity.min' => 'Miktar en az 1 olmalıdır.',
            'machine_id.required' => 'Makine ID\'si zorunludur.',
            'machine_id.exists' => 'Seçtiğiniz makine mevcut değildir.',
            'shift_id.required' => 'Vardiya ID\'si zorunludur.',
            'shift_id.exists' => 'Seçtiğiniz vardiya mevcut değildir.',
            'production_date.required' => 'Üretim tarihi zorunludur.',
            'production_date.date' => 'Geçerli bir tarih formatı kullanın.',
            'worker_id.required' => 'İşçi ID\'si zorunludur.',
            'worker_id.exists' => 'Seçtiğiniz işçi mevcut değildir.',
        ];
    }
}
