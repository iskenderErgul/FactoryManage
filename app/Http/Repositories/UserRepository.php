<?php

namespace App\Http\Repositories;

use App\DTOs\Users\LoginUserDTO;
use App\DTOs\Users\StoreUserDTO;
use App\DTOs\Users\UpdateUserDTO;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Interfaces\UserInterface;
use App\Models\User;
use App\Models\UsersLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UserRepository implements UserInterface
{

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

    public function logout(): JsonResponse
    {
        Session::flush();
        return response()->json('Çıkış İşlemi Başarılı');
    }
    public function getAllUsers(): JsonResponse
    {
        return response()->json(User::all());
    }
    public function getAllUserLogs(): JsonResponse
    {
        return response()->json(UsersLog::with('user')->get());
    }
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

        if ($request->password && !Hash::check($request->password, $user->password)) {
            $user->password = bcrypt($request->password);
        }
        $user->save();
        $this->logUserAction('Güncellendi', $user, Auth::user());

        return response()->json($user);
    }
    public function destroy($id): JsonResponse
    {
        $this->authorizeAdmin(); // Admin yetkisi kontrolü

        $user = User::findOrFail($id);
        $this->logUserAction('Silindi', $user, Auth::user());
        $user->delete();

        return response()->json(null, 204);
    }
    private function authorizeAdmin(): void
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Sadece Adminler İşlem Yapabilir');
        }
    }
    private function logUserAction($action, User $user, $admin): void
    {
        UsersLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'changes' => "Kullanıcı {$user->name} {$admin->name} tarafından {$action} yapıldı.",
        ]);
    }
}
