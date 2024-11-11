<?php

namespace App\Http\Requests\ShiftAssignments;

use Illuminate\Foundation\Http\FormRequest;

class AddShiftAssignmentRequest extends FormRequest
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
            'user_id' => 'required',
            'shift_id' => 'required',
        ];
    }
}
