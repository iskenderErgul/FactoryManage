<?php

namespace App\Domains\Users\Repositories;

use App\Domains\Users\Interfaces\UserInterface;
use App\Domains\Users\Models\User;
use App\Domains\Users\Models\UsersLog;
use App\DTOs\Users\LoginUserDTO;
use App\DTOs\Users\StoreUserDTO;
use App\DTOs\Users\UpdateUserDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UserRepository implements UserInterface
{

    /**
     * Kullanıcı giriş işlemini gerçekleştirir.
     *
     * @param LoginUserDTO $userDTO
     * @return JsonResponse
     */

    public function login(LoginUserDTO $userDTO): JsonResponse
    {
        $userControl = [
            'email' => $userDTO->email,
            'password' => $userDTO->password,
        ];

        if (Auth::attempt($userControl)) {

            return response()->json(Auth::user(),'200');
        } else {
            return response()->json('Giriş Başarısız', 401);
        }
    }

    /**
     * Kullanıcı çıkış işlemini gerçekleştirir.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Session::flush();
        return response()->json('Çıkış İşlemi Başarılı');
    }

    /**
     * Tüm kullanıcıları alır.
     *
     * @return JsonResponse
     */
    public function getAllUsers(): JsonResponse
    {
        return response()->json(User::all());
    }

    /**
     * Tüm kullanıcı loglarını alır.
     *
     * @return JsonResponse
     */
    public function getAllUserLogs(): JsonResponse
    {
        return response()->json(UsersLog::with('user')->get());
    }

    /**
     * Yeni bir kullanıcı kaydeder.
     *
     * @param StoreUserDTO $request
     * @return JsonResponse
     */
    public function store(StoreUserDTO $request): JsonResponse
    {
        $this->authorizeAdmin();

        $photoPath = null;
        if ($request->photoPath) {
            $photoPath = $this->imageUploadService->uploadImage($request->file('photo'), 'users');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt($request->password),
            'photo' => $photoPath,
        ]);
        $this->logUserAction('Kayıt Edildi', $user, Auth::user());

        return response()->json($user, 201);
    }

    /**
     * Kullanıcı bilgilerini günceller.
     *
     * @param UpdateUserDTO $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserDTO $request, $id): JsonResponse
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($id);
        if ($request->photoPath) {
            $this->imageUploadService->deleteImage($user->photo);
            $user->photo = $this->imageUploadService->uploadImage($request->file('photo'), 'users');
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = is_array($request->role) ? $request->role['name'] : $request->role;

        // Şifre alanı dolu gelirse şifreyi güncelle, boş gelirse güncelleme
        if ($request->password && !empty(trim($request->password))) {
            $user->password = bcrypt($request->password);
        }
        $user->save();
        $this->logUserAction('Güncellendi', $user, Auth::user());

        return response()->json($user);
    }

    /**
     * Kullanıcıyı siler.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->authorizeAdmin(); // Admin yetkisi kontrolü

        $user = User::findOrFail($id);
        $this->logUserAction('Silindi', $user, Auth::user());
        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * Sadece adminlerin işlem yapmasını sağlar.
     *
     * @return void
     */
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Sadece Adminler İşlem Yapabilir');
        }
    }

    /**
     * Kullanıcı işlemlerini loglar.
     *
     * @param string $action
     * @param User $user
     * @param $admin
     * @return void
     */
    private function logUserAction($action, User $user, $admin): void
    {
        UsersLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'changes' => "Kullanıcı {$user->name} {$admin->name} tarafından {$action} yapıldı.",
        ]);
    }
}
