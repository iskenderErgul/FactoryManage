<?php

namespace App\Interfaces;

use App\DTOs\Users\LoginUserDTO;
use App\DTOs\Users\StoreUserDTO;
use App\DTOs\Users\UpdateUserDTO;

use Illuminate\Http\JsonResponse;

interface UserInterface
{
    public function login(LoginUserDTO $userDTO): JsonResponse;

    public function logout(): JsonResponse;

    public function getAllUsers(): JsonResponse;

    public function getAllUserLogs(): JsonResponse;

    public function store(StoreUserDTO $request): JsonResponse;

    public function update(UpdateUserDTO $request, $id): JsonResponse;

    public function destroy($id): JsonResponse;
}
