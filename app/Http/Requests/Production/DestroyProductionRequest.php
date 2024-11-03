<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class DestroyProductionRequest extends FormRequest
{
    public function authorize(): bool
    {

        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:productions,id',
        ];
    }
}
