<?php

namespace App\Http\Requests\ShiftAssignments;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftAssignmentRequest extends FormRequest
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
            'id' => 'required|integer',
            'shift_id' => 'required|integer',
            'user_id' => 'required|integer',
            'created_at' => 'required|date',
            'updated_at' => 'required|date',
            'shift.id' => 'required|integer',
            'shift.template_id' => 'required|integer',
            'shift.date' => 'required|date',
            'shift.created_at' => 'required|date',
            'shift.updated_at' => 'required|date',
            'shift.template.id' => 'required|integer',
            'shift.template.name' => 'required|string',
            'shift.template.start_time' => 'required|date_format:H:i:s',
            'shift.template.end_time' => 'required|date_format:H:i:s',
            'shift.template.duration' => 'required|integer|min:0',
            'shift.template.created_at' => 'required|date',
            'shift.template.updated_at' => 'required|date',
        ];
    }
}
