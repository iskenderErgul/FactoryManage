<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
            'role' => 'required',
            'password' => 'nullable|string|min:3',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'İsim alanı gereklidir.',
            'email.required' => 'Email alanı gereklidir.',
            'email.unique' => 'Bu email zaten kullanılıyor.',
            'role.required' => 'Rol alanı gereklidir.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'photo.image' => 'Yalnızca resim dosyası yüklenebilir.',
            'photo.mimes' => 'Yalnızca jpeg, png, jpg ve gif dosyaları yüklenebilir.',
            'photo.max' => 'Resim dosyası 2MB\'dan küçük olmalıdır.',
        ];
    }
}
