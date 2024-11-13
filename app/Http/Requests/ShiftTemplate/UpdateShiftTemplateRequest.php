<?php

namespace App\Http\Requests\ShiftTemplate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftTemplateRequest extends FormRequest
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
            'id' => 'required|integer',
            'name' => 'required|string|max:255',

            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
            'duration' => 'required|integer|min:0',
            'created_at' => 'required|date',
            'updated_at' => 'required|date',
            'shifts' => 'required|array',
            'shifts.*.id' => 'required|integer',
            'shifts.*.template_id' => 'required|integer',
            'shifts.*.date' => 'required|date',
            'shifts.*.created_at' => 'required|date',
            'shifts.*.updated_at' => 'required|date',
        ];
    }
}
