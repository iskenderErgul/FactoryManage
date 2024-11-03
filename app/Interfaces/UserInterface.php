<?php

namespace App\Interfaces;

use App\DTOs\Users\LoginUserDTO;
use App\DTOs\Users\StoreUserDTO;
use App\DTOs\Users\UpdateUserDTO;

use Illuminate\Http\JsonResponse;

interface UserInterface
{
    /**
     * Kullanıcı giriş işlemini gerçekleştirir.
     *
     * @param LoginUserDTO $userDTO Kullanıcının giriş bilgilerini içeren DTO
     * @return JsonResponse
     */
    public function login(LoginUserDTO $userDTO): JsonResponse;

    /**
     * Kullanıcı çıkış işlemini gerçekleştirir.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse;

    /**
     * Tüm kullanıcı verilerini listeler.
     *
     * @return JsonResponse
     */
    public function getAllUsers(): JsonResponse;

    /**
     * Tüm kullanıcı loglarını listeler.
     *
     * @return JsonResponse
     */
    public function getAllUserLogs(): JsonResponse;

    /**
     * Yeni bir kullanıcı kaydı oluşturur.
     *
     * @param StoreUserDTO $request Kullanıcı bilgilerini içeren DTO
     * @return JsonResponse
     */
    public function store(StoreUserDTO $request): JsonResponse;

    /**
     * Mevcut bir kullanıcı kaydını günceller.
     *
     * @param UpdateUserDTO $request Güncellenmiş kullanıcı bilgilerini içeren DTO
     * @param int $id Güncellenmek istenen kullanıcının benzersiz kimliği
     * @return JsonResponse
     */
    public function update(UpdateUserDTO $request, int $id): JsonResponse;

    /**
     * Belirli bir kullanıcı kaydını siler.
     *
     * @param int $id Silinecek kullanıcının benzersiz kimliği
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse;
}
