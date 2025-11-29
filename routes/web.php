<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

// Resimlere erişim için route
Route::get('/resources/resimler/{path}', function ($path) {
    $filePath = resource_path('resimler/' . $path);
    
    if (!File::exists($filePath)) {
        abort(404);
    }
    
    $mimeType = File::mimeType($filePath);
    
    return Response::file($filePath, [
        'Content-Type' => $mimeType,
    ]);
})->where('path', '.*');

Route::view('{any}', 'welcome')->where('any','.*')->name('any');
