<?php

namespace App\Common\Services;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{
    /**
     * Resmi yükler ve dosya yolunu döndürür.
     *
     * @param UploadedFile $image Resim dosyası
     * @param string $directory Klasör adı (örn: 'users', 'products')
     * @return string Yüklenen dosya yolu
     */
    public function uploadImage(UploadedFile $image, string $directory): string
    {

        $fileName = time() . '_' . $image->getClientOriginalName();
        $filePath = $image->storeAs($directory, $fileName, 'public');
        return $filePath;
    }

    /**
     * Eski resmi siler.
     *
     * @param string|null $filePath Silinecek dosya yolu
     * @return void
     */
    public function deleteImage(?string $filePath)
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
    }
}
