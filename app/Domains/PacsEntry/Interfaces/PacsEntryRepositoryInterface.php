<?php

namespace App\Domains\PacsEntry\Interfaces;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface PacsEntryRepositoryInterface
{
    /**
     * Tüm PACS girişlerini alır.
     *
     * @return JsonResponse
     */
    public function getAllPacsEntries(): JsonResponse;

    /**
     * Tüm PACS giriş loglarını alır.
     *
     * @return JsonResponse
     */
    public function getAllPacsEntriesLogs(): JsonResponse;

    /**
     * Yeni bir PACS girişi oluşturur.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createPacsEntry(Request $request): JsonResponse;
}
