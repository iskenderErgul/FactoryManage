<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    protected $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }
    public function getAllUsers(): JsonResponse
    {
        return response()->json(User::all());
    }
    public function store(Request $request): JsonResponse
    {
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $this->imageUploadService->uploadImage($request->file('photo'), 'users');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt($request->password),
            'photo' => $photoPath,
        ]);

        return response()->json($user, 201);
    }
    public function update(Request $request, $id): JsonResponse
    {

        $user = User::findOrFail($id);
        if ($request->hasFile('photo')) {
            $this->imageUploadService->deleteImage($user->photo);
            $user->photo = $this->imageUploadService->uploadImage($request->file('photo'), 'users');
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role['name'];

        if ($request->password && !Hash::check($request->password, $user->password)) {
            $user->password = bcrypt($request->password);
        }
        $user->save();
        return response()->json($user);
    }

    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(null, 204);

    }
}
