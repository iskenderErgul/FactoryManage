<?php

namespace App\DTOs\Users;

use App\Http\Requests\Users\StoreUserRequest;

class StoreUserDTO
{
    public string $name;
    public string $email;
    public string $role;
    public string $password;
    public ?string $photoPath;

    public static function buildFromRequest(StoreUserRequest $request): self
    {
        $storeUserDTO = new self();
        $storeUserDTO->name = $request->input('name');
        $storeUserDTO->email = $request->input('email');
        $storeUserDTO->role = $request->input('role');
        $storeUserDTO->password = $request->input('password');
        $storeUserDTO->photoPath = $request->hasFile('photo') ? $request->file('photo')->store('users') : null; // veya başka bir dosya yükleme mantığı
        return $storeUserDTO;
    }
}
