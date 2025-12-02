<?php

namespace App\Http\Controllers;

use App\Common\Services\ImageUploadService;
use App\Http\Requests\SiteSettings\SiteSettingsBulkUpdateRequest;
use App\Http\Requests\SiteSettings\SiteSettingUploadRequest;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingsController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $imageUploadService
    ) {
    }

    /**
     * Tüm site ayarlarını döner.
     * İsteğe bağlı olarak group parametresiyle filtrelenebilir.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SiteSetting::query();

        if ($request->filled('group')) {
            $query->where('group', $request->get('group'));
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();

        return response()->json($settings);
    }

    /**
     * Tek bir ayarı günceller veya yoksa oluşturur.
     */
    public function upsert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group' => ['nullable', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
        ]);

        $setting = SiteSetting::updateOrCreate(
            [
                'group' => $validated['group'] ?? null,
                'key' => $validated['key'],
            ],
            [
                'value' => $validated['value'] ?? null,
            ]
        );

        return response()->json($setting);
    }

    /**
     * Birden fazla ayarı toplu olarak kaydeder.
     * Payload: [{ group, key, value }, ...]
     */
    public function bulkUpdate(SiteSettingsBulkUpdateRequest $request): JsonResponse
    {
        $saved = [];
        foreach ($request->validated('settings') as $item) {
            $value = $item['value'] ?? null;

            if (in_array($item['key'], ['site_logo', 'site_favicon'], true) && $value) {
                $path = parse_url($value, PHP_URL_PATH) ?? $value;
                $value = ltrim(preg_replace('#^/?storage/#', '', $path), '/');
            }
            $saved[] = SiteSetting::updateOrCreate(
                [
                    'group' => $item['group'] ?? null,
                    'key' => $item['key'],
                ],
                [
                    'value' => $value,
                ]
            );
        }

        return response()->json($saved);
    }


    public function uploadFile(SiteSettingUploadRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $path = $this->imageUploadService->uploadImage($file, 'site');

        $existing = SiteSetting::where('group', 'general')
            ->where('key', $request->input('key'))
            ->first();

        if ($existing && $existing->value) {
            $this->imageUploadService->deleteImage($existing->value);
        }

        $setting = SiteSetting::updateOrCreate(
            [
                'group' => 'general',
                'key' => $request->input('key'),
            ],
            [
                'value' => $path,
            ]
        );

        // site_favicon güncellendiyse, public/sekmeicon.png dosyasını da güncelle
        if ($request->input('key') === 'site_favicon') {
            $fullPath = Storage::disk('public')->path($path);
            if (file_exists($fullPath)) {
                @copy($fullPath, public_path('sekmeicon.png'));
            }
        }

        // site_logo güncellendiyse, public/Logo.png dosyasını da güncelle
        if ($request->input('key') === 'site_logo') {
            $fullPath = Storage::disk('public')->path($path);
            if (file_exists($fullPath)) {
                @copy($fullPath, public_path('Logo.png'));
            }
        }

        $url = Storage::disk('public')->url($path);

        return response()->json([
            'path' => $path,
            'url' => $url,
            'setting' => $setting,
        ]);
    }
}


