<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class GalleryController extends Controller
{
    /**
     * Tüm resimleri marka bazlı döner
     */
    public function index(): JsonResponse
    {
        $imagesPath = resource_path('resimler');
        $galleries = [];

        if (!File::exists($imagesPath)) {
            return response()->json(['galleries' => []]);
        }

        $brands = File::directories($imagesPath);

        foreach ($brands as $brandPath) {
            $brandName = basename($brandPath);
            
            // homepage-slider klasörünü atla
            if ($brandName === 'homepage-slider') {
                continue;
            }
            
            $brandImages = [];

            // Marka klasöründeki tüm resimleri topla
            $this->collectImages($brandPath, $brandName, $brandImages);

            if (!empty($brandImages)) {
                $galleries[] = [
                    'brand' => $brandName,
                    'images' => $brandImages
                ];
            }
        }

        return response()->json(['galleries' => $galleries]);
    }

    /**
     * Homepage slider için resim ve videoları döner
     */
    public function getHomepageSlider(): JsonResponse
    {
        $sliderPath = resource_path('resimler/homepage-slider');
        $slides = [];

        if (!File::exists($sliderPath)) {
            return response()->json(['slides' => []]);
        }

        $items = File::allFiles($sliderPath);

        foreach ($items as $item) {
            $extension = strtolower($item->getExtension());
            $relativePath = str_replace(resource_path('resimler'), '', $item->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);
            $relativePath = ltrim($relativePath, '/');
            $publicPath = '/resources/resimler/' . $relativePath;

            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $slides[] = [
                    'type' => 'image',
                    'url' => $publicPath,
                    'name' => $item->getFilename()
                ];
            } elseif (in_array($extension, ['mp4', 'webm', 'ogg'])) {
                $slides[] = [
                    'type' => 'video',
                    'url' => $publicPath,
                    'name' => $item->getFilename()
                ];
            }
        }

        return response()->json(['slides' => $slides]);
    }

    /**
     * Belirli bir ürün için resimleri döner (ürün adına göre klasör araması)
     */
    public function getProductImages(string $productName): JsonResponse
    {
        $imagesPath = resource_path('resimler');
        $productImages = [];

        if (!File::exists($imagesPath)) {
            return response()->json(['brands' => []]);
        }

        // Ürün adını normalize et (küçük harf, Türkçe karakterleri düzelt, boşlukları tire ile değiştir)
        $normalizedProductName = $this->normalizeProductName($productName);
        
        // Önce direkt ürün klasörünü ara
        $productFolderPath = $imagesPath . '/' . $normalizedProductName;
        
        if (File::exists($productFolderPath) && File::isDirectory($productFolderPath)) {
            // Önce alt klasörleri kontrol et
            $subDirs = File::directories($productFolderPath);
            
            if (!empty($subDirs)) {
                // Alt klasörler varsa (marka klasörleri), marka bazlı grupla
                foreach ($subDirs as $subDir) {
                    $brandName = basename($subDir);
                    $brandImages = [];
                    $this->collectImages($subDir, $brandName, $brandImages);
                    
                    if (!empty($brandImages)) {
                        $productImages[] = [
                            'brand' => $brandName,
                            'images' => $brandImages
                        ];
                    }
                }
            } else {
                // Alt klasör yoksa direkt klasördeki resimleri topla
                $allImages = [];
                $this->collectImages($productFolderPath, $normalizedProductName, $allImages);
                
                if (!empty($allImages)) {
                    $productImages[] = [
                        'brand' => $normalizedProductName,
                        'images' => $allImages
                    ];
                }
            }
        } else {
            // Eski yapı: Marka klasörlerinde ara (geriye dönük uyumluluk)
            $brands = File::directories($imagesPath);
            
            foreach ($brands as $brandPath) {
                $brandName = basename($brandPath);
                $brandImages = [];
                
                // Marka klasöründeki tüm resimleri topla
                $this->collectImages($brandPath, $brandName, $brandImages);
                
                if (!empty($brandImages)) {
                    $productImages[] = [
                        'brand' => $brandName,
                        'images' => $brandImages
                    ];
                }
            }
        }

        return response()->json(['brands' => $productImages]);
    }

    /**
     * Ürün adını normalize eder (klasör adına uygun hale getirir)
     */
    private function normalizeProductName(string $productName): string
    {
        // Özel eşleşmeler (ürün adı -> klasör adı)
        $specialMappings = [
            'hastane torbası' => 'hastane-torba',
            'hastane-torbasi' => 'hastane-torba',
        ];
        
        $lowerName = mb_strtolower($productName, 'UTF-8');
        if (isset($specialMappings[$lowerName])) {
            return $specialMappings[$lowerName];
        }
        
        // Türkçe karakterleri düzelt
        $turkishChars = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü'];
        $englishChars = ['c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'O', 'S', 'U'];
        $normalized = str_replace($turkishChars, $englishChars, $productName);
        
        // Küçük harfe çevir
        $normalized = mb_strtolower($normalized, 'UTF-8');
        
        // Özel karakterleri temizle ve boşlukları tire ile değiştir
        $normalized = preg_replace('/[^a-z0-9\s-]/', '', $normalized);
        $normalized = preg_replace('/\s+/', '-', trim($normalized));
        
        // Çoklu tireleri tek tire yap
        $normalized = preg_replace('/-+/', '-', $normalized);
        
        return $normalized;
    }

    /**
     * Klasör yapısından resimleri toplar
     */
    private function collectImages(string $path, string $brandName, array &$images): void
    {
        $items = File::allFiles($path);

        foreach ($items as $item) {
            $extension = strtolower($item->getExtension());
            
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $relativePath = str_replace(resource_path('resimler'), '', $item->getPathname());
                $relativePath = str_replace('\\', '/', $relativePath);
                $relativePath = ltrim($relativePath, '/');
                
                // Public URL için path
                $publicPath = '/resources/resimler/' . $relativePath;

                $folderName = basename($item->getPath());
                
                $images[] = [
                    'url' => $publicPath,
                    'path' => $relativePath,
                    'name' => $item->getFilename(),
                    'folder' => $folderName !== $brandName ? $folderName : null,
                    'full_path' => $item->getPathname()
                ];
            }
        }

        // Alt klasörleri de kontrol et
        $directories = File::directories($path);
        foreach ($directories as $directory) {
            $this->collectImages($directory, $brandName, $images);
        }
    }
}

