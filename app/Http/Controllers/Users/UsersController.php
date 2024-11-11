<?php

namespace App\Http\Controllers\Users;

use App\Common\Services\ImageUploadService;
use App\Domains\Users\Models\User;
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

    /**
     * Tüm kullanıcıları döner.
     *
     * @return JsonResponse
     */
    public function getAllUsers(): JsonResponse
    {
        return $this->userRepository->getAllUsers();
    }

    /**
     * Tüm kullanıcı loglarını döner.
     *
     * @return JsonResponse
     */
    public function getAllUserLogs(): JsonResponse
    {
        return $this->userRepository->getAllUserLogs();
    }

    /**
     * Yeni bir kullanıcı kaydeder.
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        return $this->userRepository->store(StoreUserDTO::buildFromRequest($request));
    }

    /**
     * Kullanıcıyı günceller.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        return $this->userRepository->update(UpdateUserDTO::buildFromRequest($request), $id);
    }


    /**
     * Belirtilen kullanıcıyı siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        return $this->userRepository->destroy($id);
    }


    public function getAllWorkers(): JsonResponse
    {
        $workers = User::where('role', 'worker')->get();
        return response()->json($workers);
    }



}
