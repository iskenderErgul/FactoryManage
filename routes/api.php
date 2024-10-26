<?php

use App\Http\Controllers\AudioTranscriptionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatGptController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Users\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/getAllUsers', [UsersController::class, 'getAllUsers']);
    Route::put('/users/{id}', [UsersController::class, 'update']);
    Route::delete('/users/{id}', [UsersController::class, 'destroy']);
    Route::post('/createUsers', [UsersController::class, 'store']);



    Route::get('/getAllUserLogs',[UsersController::class, 'getAllUserLogs']);



    Route::post('/transcribe', [AudioTranscriptionController::class, 'transcribe']);


});



Route::post('/login',[LoginController::class,'login']);





