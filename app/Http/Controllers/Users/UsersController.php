<?php

namespace App\Http\Controllers\Users;

use App\Common\Services\ImageUploadService;
use App\Domains\Users\Repositories\UserRepository;
use App\DTOs\Users\StoreUserDTO;
use App\DTOs\Users\UpdateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use Illuminate\Http\JsonResponse;


class UsersController extends Controller
{
    protected $imageUploadService;
    protected UserRepository $userRepository;

    public function __construct(ImageUploadService $imageUploadService, UserRepository $userRepository)
    {
        $this->imageUploadService = $imageUploadService;
        $this->userRepository = $userRepository;
    }
    public function getAllUsers(): JsonResponse
    {
        return $this->userRepository->getAllUsers();
    }
    public function getAllUserLogs(): JsonResponse
    {
        return $this->userRepository->getAllUserLogs();
    }
    public function store(StoreUserRequest $request): JsonResponse
    {
        return $this->userRepository->store(StoreUserDTO::buildFromRequest($request));
    }
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        return $this->userRepository->update(UpdateUserDTO::buildFromRequest($request), $id);
    }
    public function destroy($id): JsonResponse
    {
        return $this->userRepository->destroy($id);
    }



}
