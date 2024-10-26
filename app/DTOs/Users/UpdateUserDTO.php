<?php

namespace App\DTOs\Users;

use App\Http\Requests\Users\UpdateUserRequest;

class UpdateUserDTO
{
    public string $name;
    public string $email;
    public string $role;
    public ?string $password;
    public ?string $photoPath;

    public static function buildFromRequest(UpdateUserRequest $request): self
    {
        $updateUserDTO = new self();
        $updateUserDTO->name = $request->input('name');
        $updateUserDTO->email = $request->input('email');
        $updateUserDTO->role = $request->input('role')['name'];
        $updateUserDTO->password = $request->input('password') ?: null;
        $updateUserDTO->photoPath = $request->hasFile('photo') ? $request->file('photo')->store('users') : null;
        return $updateUserDTO;
    }
}
